<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Kategori',
            'Deskripsi',
            'Jumlah (Rp)',
        ];
    }

    public function map($transaction): array
    {
        return [
            Carbon::parse($transaction->date)->translatedFormat('d F Y'),
            $transaction->type == 'income' ? 'Pemasukan' : 'Pengeluaran',
            $transaction->category->name ?? 'Lainnya',
            $transaction->description,
            $transaction->amount,
        ];
    }
}
