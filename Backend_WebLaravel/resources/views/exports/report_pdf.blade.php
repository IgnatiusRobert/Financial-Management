<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d9488; padding-bottom: 15px; }
        .logo { font-size: 24px; font-weight: bold; color: #0d9488; margin-bottom: 5px; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 5px 0; }
        .summary { display: flex; margin-bottom: 30px; background: #f3f4f6; padding: 15px; border-radius: 8px; }
        .summary-box { width: 33%; text-align: center; display: inline-block; }
        .summary-title { font-size: 11px; color: #6b7280; text-transform: uppercase; }
        .summary-value { font-size: 16px; font-weight: bold; margin-top: 5px; }
        .text-green { color: #10b981; }
        .text-red { color: #f43f5e; }
        .text-brand { color: #0d9488; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        table.data th { background-color: #f9fafb; font-weight: bold; }
        .footer { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 50px; border-top: 1px solid #e5e7eb; padding-top: 15px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">FINMAN</div>
        <div style="font-size: 16px; color: #4b5563;">Laporan Keuangan Pribadi</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Nama:</strong></td>
                <td>{{ $user->name }}</td>
                <td style="width: 15%;"><strong>Tanggal Cetak:</strong></td>
                <td>{{ $date_generated }}</td>
            </tr>
            <tr>
                <td><strong>Periode:</strong></td>
                <td colspan="3">
                    @if($filter == 'daily') Harian
                    @elseif($filter == 'weekly') Mingguan
                    @elseif($filter == 'monthly') Bulanan ({{ now()->translatedFormat('F Y') }})
                    @elseif($filter == 'yearly') Tahunan ({{ now()->year }})
                    @elseif($filter == 'custom') {{ $start_date }} s/d {{ $end_date }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="summary-title">Pemasukan</div>
            <div class="summary-value text-green">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Pengeluaran</div>
            <div class="summary-value text-red">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-title">Arus Kas Bersih</div>
            <div class="summary-value {{ $netFlow >= 0 ? 'text-brand' : 'text-red' }}">Rp {{ number_format($netFlow, 0, ',', '.') }}</div>
        </div>
    </div>

    <h3 style="color: #111827; margin-bottom: 10px;">Ringkasan Pengeluaran per Kategori</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Total Pengeluaran (Rp)</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expensesByCategory as $expense)
            <tr>
                <td>{{ $expense['name'] }}</td>
                <td>Rp {{ number_format($expense['total'], 0, ',', '.') }}</td>
                <td>{{ $totalExpense > 0 ? round(($expense['total'] / $totalExpense) * 100) : 0 }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center;">Tidak ada pengeluaran pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="color: #111827; margin-bottom: 10px;">Rincian Transaksi</h3>
    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th style="text-align: right;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
            <tr>
                <td>{{ \Carbon\Carbon::parse($t->date)->translatedFormat('d M Y') }}</td>
                <td style="color: {{ $t->type == 'income' ? '#10b981' : '#f43f5e' }};">{{ $t->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                <td>{{ $t->category->name ?? 'Lainnya' }}</td>
                <td>{{ $t->description }}</td>
                <td style="text-align: right;">{{ number_format($t->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Manajemen Keuangan pada {{ $date_generated }}.
    </div>
</body>
</html>
