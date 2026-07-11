<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ApiReportController extends Controller
{
    /**
     * Return report data filtered by period.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getReportData($request);

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil diambil.',
            'data' => [
                'filter' => $request->input('filter', 'monthly'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'total_income' => (float) $data['totalIncome'],
                'total_expense' => (float) $data['totalExpense'],
                'net_flow' => (float) $data['netFlow'],
                'expenses_by_category' => $data['expensesByCategory']->map(function ($item) use ($data) {
                    $percentage = (float) $data['totalExpense'] > 0
                        ? round(($item['total'] / $data['totalExpense']) * 100, 1)
                        : 0;

                    return [
                        'name' => $item['name'],
                        'color' => $item['color'],
                        'total' => (float) $item['total'],
                        'percentage' => $percentage,
                    ];
                })->values(),
                'transactions' => $data['transactions']->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'description' => $t->description,
                        'amount' => (float) $t->amount,
                        'type' => $t->type,
                        'date' => $t->date,
                        'category' => $t->category ? [
                            'id' => $t->category->id,
                            'name' => $t->category->name,
                            'color' => $t->category->color ?? null,
                        ] : null,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * Export report file (pdf, excel, csv).
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'pdf');

        if (! in_array($format, ['pdf', 'excel', 'csv'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format export tidak valid. Gunakan: pdf, excel, atau csv.',
                'data' => null,
            ], 422);
        }

        $transactions = $this->getFilteredTransactions($request)->get();

        if ($format === 'pdf') {
            $data = $this->getReportData($request);
            $data['user'] = auth()->user();
            $data['filter'] = $request->input('filter', 'monthly');
            $data['start_date'] = $request->input('start_date');
            $data['end_date'] = $request->input('end_date');
            $data['date_generated'] = Carbon::now()->translatedFormat('d F Y H:i');

            $pdf = Pdf::loadView('exports.report_pdf', $data);
            return $pdf->download('laporan_keuangan_' . now()->format('Ymd_His') . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new TransactionsExport($transactions),
                'laporan_keuangan_' . now()->format('Ymd_His') . '.xlsx'
            );
        }

        // csv
        return Excel::download(
            new TransactionsExport($transactions),
            'laporan_keuangan_' . now()->format('Ymd_His') . '.csv'
        );
    }

    /**
     * Get filtered transactions query.
     * Logic copied from ReportController.
     */
    private function getFilteredTransactions(Request $request)
    {
        $userId = auth()->id();
        $query = Transaction::where('user_id', $userId)->with('category')->orderBy('date', 'desc');

        $filter = $request->input('filter', 'monthly');

        if ($filter == 'daily') {
            $query->whereDate('date', Carbon::today());
        } elseif ($filter == 'weekly') {
            $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter == 'monthly') {
            $query->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year);
        } elseif ($filter == 'yearly') {
            $query->whereYear('date', Carbon::now()->year);
        } elseif ($filter == 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return $query;
    }

    /**
     * Get report data from filtered transactions.
     * Logic copied from ReportController.
     */
    private function getReportData(Request $request): array
    {
        $transactionsQuery = $this->getFilteredTransactions($request);
        $transactions = $transactionsQuery->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netFlow = $totalIncome - $totalExpense;

        $expensesByCategory = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->category->name ?? 'Lainnya',
                    'color' => $group->first()->category->color ?? '#9ca3af',
                    'total' => $group->sum('amount'),
                ];
            })->sortByDesc('total')->values();

        return compact('transactions', 'totalIncome', 'totalExpense', 'netFlow', 'expensesByCategory');
    }
}
