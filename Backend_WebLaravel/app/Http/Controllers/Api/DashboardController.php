<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return dashboard summary data.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $filter = $request->input('filter', 'monthly');

        $applyFilter = function($query) use ($filter, $request) {
            if ($filter == 'daily') {
                $query->whereDate('date', \Carbon\Carbon::today());
            } elseif ($filter == 'weekly') {
                $query->whereBetween('date', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($filter == 'monthly') {
                $query->whereMonth('date', \Carbon\Carbon::now()->month)->whereYear('date', \Carbon\Carbon::now()->year);
            } elseif ($filter == 'yearly') {
                $query->whereYear('date', \Carbon\Carbon::now()->year);
            } elseif ($filter == 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }
            return $query;
        };

        // Filtered income & expense
        $pemasukan = $applyFilter(Transaction::where('user_id', $userId)->where('type', 'income'))->sum('amount');
        $pengeluaran = $applyFilter(Transaction::where('user_id', $userId)->where('type', 'expense'))->sum('amount');

        // All-time balance
        $totalIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $saldoSaatIni = $totalIncome - $totalExpense;

        // Savings
        $tabungan = SavingsGoal::where('user_id', $userId)->sum('current_amount');
        $targetTabungan = SavingsGoal::where('user_id', $userId)->get();

        // Budget utilization (using monthly budget regardless of filter for now, or match it? usually budget is monthly)
        $totalBudget = Budget::where('user_id', $userId)
            ->where('period', 'monthly')
            ->sum('amount');
        $budgetUtilization = $totalBudget > 0
            ? min(100, round(($pengeluaran / $totalBudget) * 100))
            : 0;

        // Recent transactions (filtered)
        $recentTransactions = $applyFilter(Transaction::where('user_id', $userId))
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Top expenses by category (filtered)
        $topExpenses = $applyFilter(Transaction::where('user_id', $userId)->where('type', 'expense'))
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(3)
            ->with('category')
            ->get();

        // Health score
        $healthScore = 50;
        if ($pemasukan > 0) {
            $savingsRatio = ($pemasukan - $pengeluaran) / $pemasukan;
            $healthScore = min(100, max(0, 50 + ($savingsRatio * 100)));
        }

        // Savings goals with progress
        $savingsGoals = $targetTabungan->take(4)->map(function ($goal) {
            $progress = (float) $goal->target_amount > 0
                ? min(100, round(($goal->current_amount / $goal->target_amount) * 100))
                : 0;

            return [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => $goal->target_amount,
                'current_amount' => $goal->current_amount,
                'target_date' => $goal->target_date,
                'progress_percentage' => $progress,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'saldo_saat_ini' => (float) $saldoSaatIni,
                'pemasukan' => (float) $pemasukan,
                'pengeluaran' => (float) $pengeluaran,
                'tabungan' => (float) $tabungan,
                'health_score' => round($healthScore),
                'top_expenses' => $topExpenses->map(function ($item) {
                    return [
                        'category_id' => $item->category_id,
                        'category_name' => $item->category->name ?? 'Lainnya',
                        'category_color' => $item->category->color ?? '#9ca3af',
                        'total' => (float) $item->total,
                    ];
                })->values(),
                'recent_transactions' => $recentTransactions->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'description' => $t->description,
                        'amount' => (float) $t->amount,
                        'type' => $t->type,
                        'date' => $t->date,
                        'category' => $t->category ? [
                            'id' => $t->category->id,
                            'name' => $t->category->name,
                            'icon' => $t->category->icon ?? null,
                            'color' => $t->category->color ?? null,
                        ] : null,
                    ];
                })->values(),
                'savings_goals' => $savingsGoals,
                'budget_utilization' => (float) $budgetUtilization,
            ],
        ]);
    }
}
