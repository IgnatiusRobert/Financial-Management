<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Services\AiTipsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiTipsController extends Controller
{
    /**
     * Return AI-generated financial tips.
     * Logic copied from web.php dashboard closure (lines 99-146).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();

        // Build financial context (same as dashboard)
        $pemasukan = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $pengeluaran = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $tabungan = SavingsGoal::where('user_id', $userId)->sum('current_amount');
        $targetTabungan = SavingsGoal::where('user_id', $userId)->get();

        $totalBudget = Budget::where('user_id', $userId)
            ->where('period', 'monthly')
            ->sum('amount');

        $budgetUtilization = $totalBudget > 0
            ? min(100, round(($pengeluaran / $totalBudget) * 100))
            : 0;

        $topExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(3)
            ->with('category')
            ->get();

        $topExpenseCategory = optional($topExpenses->first()?->category)->name ?? 'pengeluaran terbesar';
        $topExpenseShare = $pengeluaran > 0
            ? round((((float) ($topExpenses->first()->total ?? 0)) / $pengeluaran) * 100)
            : 0;

        $averageGoalProgress = $targetTabungan->count() > 0
            ? round($targetTabungan->avg(function ($goal) {
                if ((float) $goal->target_amount <= 0) {
                    return 0;
                }
                return min(100, (($goal->current_amount / $goal->target_amount) * 100));
            }))
            : 0;

        $goalNearestDeadline = optional(
            $targetTabungan
                ->filter(fn ($goal) => ! empty($goal->target_date))
                ->sortBy('target_date')
                ->first()
        )->name;

        $goalNames = $targetTabungan
            ->pluck('name')
            ->filter()
            ->take(4)
            ->values()
            ->all();

        $budgetCategoryCount = Budget::where('user_id', $userId)->count();
        $recentExpenseCount = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereDate('date', '>=', now()->subDays(14)->toDateString())
            ->count();

        $aiContext = [
            'month' => now()->translatedFormat('F Y'),
            'income' => round((float) $pemasukan, 2),
            'expense' => round((float) $pengeluaran, 2),
            'net_cashflow' => round((float) ($pemasukan - $pengeluaran), 2),
            'expense_ratio_percent' => $pemasukan > 0 ? round(($pengeluaran / $pemasukan) * 100) : 0,
            'top_expense_category' => $topExpenseCategory,
            'top_expense_total' => round((float) ($topExpenses->first()->total ?? 0), 2),
            'top_expense_share_percent' => (int) $topExpenseShare,
            'goals_count' => $targetTabungan->count(),
            'average_goal_progress_percent' => (int) $averageGoalProgress,
            'nearest_goal_deadline_name' => $goalNearestDeadline,
            'total_savings' => round((float) $tabungan, 2),
            'total_budget' => round((float) $totalBudget, 2),
            'budget_utilization_percent' => (int) $budgetUtilization,
            'budget_category_count' => (int) $budgetCategoryCount,
            'recent_expense_count_last_14_days' => (int) $recentExpenseCount,
            'user_input_names' => array_values(array_filter(array_unique(array_merge(
                [$topExpenseCategory, (string) $goalNearestDeadline],
                $goalNames
            )))),
        ];

        // AI tips generation with caching
        $aiTipsCacheKey = 'dashboard.ai_tips.' . $userId . '.' . now()->format('Y-m');
        $refreshRequested = $request->boolean('refresh');
        $aiService = app(AiTipsService::class);
        $cachedTips = Cache::get($aiTipsCacheKey, []);
        $cachedTips = is_array($cachedTips) ? $cachedTips : [];
        $aiTips = [];
        $aiTipsError = null;
        $aiStatus = 'ai_live';

        if (! $refreshRequested && ! empty($cachedTips)) {
            $aiTips = $cachedTips;
            $aiStatus = 'ai_cached';
        } else {
            $aiTipsResult = $aiService->generateTipsResult($aiContext, $cachedTips, $refreshRequested);
            $aiTips = is_array($aiTipsResult['tips'] ?? null) ? $aiTipsResult['tips'] : [];

            if (! empty($aiTips)) {
                Cache::put($aiTipsCacheKey, $aiTips, now()->addHours(8));
                $aiStatus = 'ai_live';
            } else {
                $aiTips = ! empty($cachedTips) ? $cachedTips : $aiService->fallbackTips($aiContext);
                $aiStatus = ! empty($cachedTips) ? 'ai_cached_error' : 'fallback_error';
                $aiTipsError = (string) ($aiTipsResult['error'] ?? 'AI provider unavailable');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tips AI berhasil diambil.',
            'data' => [
                'tips' => $aiTips,
                'ai_status' => $aiStatus,
                'ai_error' => $aiTipsError,
            ],
        ]);
    }
}
