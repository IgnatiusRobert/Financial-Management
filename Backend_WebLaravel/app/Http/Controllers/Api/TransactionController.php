<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use App\Notifications\BudgetUsageAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * List user's transactions with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::where('user_id', auth()->id())
            ->with('category')
            ->orderBy('date', 'desc');

        // Filter by type
        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi berhasil diambil.',
            'data' => $transactions,
        ]);
    }

    /**
     * Create a new transaction.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:income,expense'],
            'category_id' => ['required', 'exists:categories,id'],
        ], [
            'description.required' => 'Deskripsi wajib diisi.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah tidak boleh kurang dari 0.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'type.required' => 'Tipe transaksi wajib diisi.',
            'type.in' => 'Tipe transaksi harus income atau expense.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
        ]);

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'category_id' => $validated['category_id'],
        ]);

        $this->notifyBudgetUsageIfNeeded($validated['type']);

        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditambahkan.',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Show a single transaction.
     */
    public function show(string $id): JsonResponse
    {
        $transaction = Transaction::where('user_id', auth()->id())
            ->with('category')
            ->find($id);

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil.',
            'data' => $transaction,
        ]);
    }

    /**
     * Update a transaction.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::where('user_id', auth()->id())->find($id);

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'date' => ['sometimes', 'required', 'date'],
            'type' => ['sometimes', 'required', 'in:income,expense'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
        ], [
            'description.required' => 'Deskripsi wajib diisi.',
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah tidak boleh kurang dari 0.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'type.required' => 'Tipe transaksi wajib diisi.',
            'type.in' => 'Tipe transaksi harus income atau expense.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
        ]);

        $transaction->update($validated);

        $this->notifyBudgetUsageIfNeeded($transaction->type);

        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => $transaction,
        ]);
    }

    /**
     * Delete a transaction.
     */
    public function destroy(string $id): JsonResponse
    {
        $transaction = Transaction::where('user_id', auth()->id())->find($id);

        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus.',
            'data' => null,
        ]);
    }

    /**
     * Notify user if monthly budget usage >= 80%.
     * Logic copied from TransactionManager Livewire component.
     */
    private function notifyBudgetUsageIfNeeded(string $type): void
    {
        if ($type !== 'expense') {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $totalBudget = Budget::where('user_id', $user->id)
            ->where('period', 'monthly')
            ->sum('amount');

        if ((float) $totalBudget <= 0) {
            return;
        }

        $monthExpense = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $utilizationPercent = ($monthExpense / $totalBudget) * 100;
        if ($utilizationPercent < 80) {
            return;
        }

        $alreadyNotifiedToday = $user->unreadNotifications()
            ->where('type', BudgetUsageAlert::class)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyNotifiedToday) {
            return;
        }

        $user->notify(new BudgetUsageAlert(
            utilizationPercent: (float) $utilizationPercent,
            spent: (float) $monthExpense,
            budget: (float) $totalBudget
        ));
    }
}
