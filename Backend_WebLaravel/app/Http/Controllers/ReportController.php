<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
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

    private function getReportData(Request $request)
    {
        $transactionsQuery = $this->getFilteredTransactions($request);
        $transactions = $transactionsQuery->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netFlow = $totalIncome - $totalExpense;

        // Group by category for expenses
        $expensesByCategory = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->category->name ?? 'Lainnya',
                    'color' => $group->first()->category->color ?? '#9ca3af',
                    'total' => $group->sum('amount')
                ];
            })->sortByDesc('total')->values();

        return compact('transactions', 'totalIncome', 'totalExpense', 'netFlow', 'expensesByCategory');
    }

    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        $data['filter'] = $request->input('filter', 'monthly');
        $data['start_date'] = $request->input('start_date');
        $data['end_date'] = $request->input('end_date');
        return view('reports', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['user'] = auth()->user();
        $data['filter'] = $request->input('filter', 'monthly');
        $data['start_date'] = $request->input('start_date');
        $data['end_date'] = $request->input('end_date');
        $data['date_generated'] = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = Pdf::loadView('exports.report_pdf', $data);
        return $pdf->download('laporan_keuangan_'.now()->format('Ymd_His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $transactions = $this->getFilteredTransactions($request)->get();
        return Excel::download(new TransactionsExport($transactions), 'laporan_keuangan_'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $transactions = $this->getFilteredTransactions($request)->get();
        return Excel::download(new TransactionsExport($transactions), 'laporan_keuangan_'.now()->format('Ymd_His').'.csv');
    }
}
