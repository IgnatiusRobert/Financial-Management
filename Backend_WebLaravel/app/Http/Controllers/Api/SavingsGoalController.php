<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use App\Notifications\SavingsGoalReached;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsGoalController extends Controller
{
    /**
     * List user's savings goals with progress percentage.
     */
    public function index(): JsonResponse
    {
        $goals = SavingsGoal::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($goal) {
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => (float) $goal->target_amount,
                    'current_amount' => (float) $goal->current_amount,
                    'target_date' => $goal->target_date,
                    'progress_percentage' => (float) $goal->target_amount > 0
                        ? min(100, round(($goal->current_amount / $goal->target_amount) * 100))
                        : 0,
                    'created_at' => $goal->created_at,
                    'updated_at' => $goal->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar target tabungan berhasil diambil.',
            'data' => $goals,
        ]);
    }

    /**
     * Create a new savings goal.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
        ], [
            'name.required' => 'Nama target tabungan wajib diisi.',
            'target_amount.required' => 'Jumlah target wajib diisi.',
            'target_amount.numeric' => 'Jumlah target harus berupa angka.',
            'target_amount.min' => 'Jumlah target tidak boleh kurang dari 0.',
            'current_amount.numeric' => 'Jumlah saat ini harus berupa angka.',
            'current_amount.min' => 'Jumlah saat ini tidak boleh kurang dari 0.',
            'target_date.date' => 'Format tanggal target tidak valid.',
        ]);

        $goal = SavingsGoal::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'target_amount' => $validated['target_amount'],
            'current_amount' => $validated['current_amount'] ?? 0,
            'target_date' => $validated['target_date'] ?? null,
        ]);

        $this->notifyIfGoalReached($goal);

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil dibuat.',
            'data' => [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'target_date' => $goal->target_date,
                'progress_percentage' => (float) $goal->target_amount > 0
                    ? min(100, round(($goal->current_amount / $goal->target_amount) * 100))
                    : 0,
                'created_at' => $goal->created_at,
                'updated_at' => $goal->updated_at,
            ],
        ], 201);
    }

    /**
     * Show a single savings goal.
     */
    public function show(string $id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', auth()->id())->find($id);

        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail target tabungan berhasil diambil.',
            'data' => [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'target_date' => $goal->target_date,
                'progress_percentage' => (float) $goal->target_amount > 0
                    ? min(100, round(($goal->current_amount / $goal->target_amount) * 100))
                    : 0,
                'created_at' => $goal->created_at,
                'updated_at' => $goal->updated_at,
            ],
        ]);
    }

    /**
     * Update a savings goal.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', auth()->id())->find($id);

        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
        ], [
            'name.required' => 'Nama target tabungan wajib diisi.',
            'target_amount.required' => 'Jumlah target wajib diisi.',
            'target_amount.numeric' => 'Jumlah target harus berupa angka.',
            'target_amount.min' => 'Jumlah target tidak boleh kurang dari 0.',
            'current_amount.numeric' => 'Jumlah saat ini harus berupa angka.',
            'current_amount.min' => 'Jumlah saat ini tidak boleh kurang dari 0.',
            'target_date.date' => 'Format tanggal target tidak valid.',
        ]);

        $goal->update($validated);
        $goal->refresh();

        $this->notifyIfGoalReached($goal);

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil diperbarui.',
            'data' => [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'target_date' => $goal->target_date,
                'progress_percentage' => (float) $goal->target_amount > 0
                    ? min(100, round(($goal->current_amount / $goal->target_amount) * 100))
                    : 0,
                'created_at' => $goal->created_at,
                'updated_at' => $goal->updated_at,
            ],
        ]);
    }

    /**
     * Delete a savings goal.
     */
    public function destroy(string $id): JsonResponse
    {
        $goal = SavingsGoal::where('user_id', auth()->id())->find($id);

        if (! $goal) {
            return response()->json([
                'success' => false,
                'message' => 'Target tabungan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $goal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Target tabungan berhasil dihapus.',
            'data' => null,
        ]);
    }

    /**
     * Notify user if savings goal target has been reached.
     * Logic copied from SavingsGoalManager Livewire component.
     */
    private function notifyIfGoalReached(SavingsGoal $goal): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if ((float) $goal->target_amount <= 0 || (float) $goal->current_amount < (float) $goal->target_amount) {
            return;
        }

        $alreadyNotified = $user->unreadNotifications()
            ->where('type', SavingsGoalReached::class)
            ->whereDate('created_at', now()->toDateString())
            ->where('data', 'like', '%"goal_name":"' . $goal->name . '"%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $user->notify(new SavingsGoalReached(
            goalName: (string) $goal->name,
            targetAmount: (float) $goal->target_amount
        ));
    }
}
