<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Keuangan</h1>
                @php
                    $periodText = 'Bulan Ini';
                    if (($filter ?? 'monthly') == 'daily') $periodText = 'Hari Ini';
                    elseif (($filter ?? 'monthly') == 'weekly') $periodText = 'Minggu Ini';
                    elseif (($filter ?? 'monthly') == 'yearly') $periodText = 'Tahun Ini';
                    elseif (($filter ?? 'monthly') == 'custom') {
                        $start = !empty($start_date) ? \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y') : '-';
                        $end = !empty($end_date) ? \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y') : '-';
                        $periodText = "$start s/d $end";
                    }
                @endphp
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Menampilkan data keuangan untuk periode: <span class="font-semibold">{{ $periodText }}</span></p>
            </div>
            
            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.pdf', request()->all()) }}" class="flex items-center gap-2 bg-red-50 text-red-600 border border-red-100 px-4 py-2 rounded-xl shadow-sm hover:bg-red-100 transition-colors text-sm font-medium dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
                    <x-heroicon-o-document-text class="w-4 h-4" />
                    PDF
                </a>
                <a href="{{ route('reports.excel', request()->all()) }}" class="flex items-center gap-2 bg-green-50 text-green-600 border border-green-100 px-4 py-2 rounded-xl shadow-sm hover:bg-green-100 transition-colors text-sm font-medium dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                    <x-heroicon-o-table-cells class="w-4 h-4" />
                    Excel
                </a>
                <a href="{{ route('reports.csv', request()->all()) }}" class="flex items-center gap-2 bg-gray-50 text-gray-600 border border-gray-200 px-4 py-2 rounded-xl shadow-sm hover:bg-gray-100 transition-colors text-sm font-medium dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700">
                    <x-heroicon-o-document-chart-bar class="w-4 h-4" />
                    CSV
                </a>
            </div>
        </div>

        @php
            $formatRupiah = function($angka) {
                return 'Rp ' . number_format($angka, 0, ',', '.');
            };
        @endphp

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
            <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-end gap-4" x-data="{ filter: '{{ $filter }}' }">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                    <select name="filter" x-model="filter" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="daily">Hari Ini</option>
                        <option value="weekly">Minggu Ini</option>
                        <option value="monthly">Bulan Ini</option>
                        <option value="yearly">Tahun Ini</option>
                        <option value="custom">Rentang Kustom</option>
                    </select>
                </div>
                
                <template x-if="filter === 'custom'">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mulai Tanggal</label>
                        <input type="date" name="start_date" value="{{ $start_date }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </template>
                
                <template x-if="filter === 'custom'">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $end_date }}" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </template>

                <button type="submit" class="bg-brand-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm flex items-center justify-center gap-2">
                    <x-heroicon-o-funnel class="w-5 h-5" />
                    <span>Terapkan Filter</span>
                </button>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-500/10 flex items-center justify-center text-green-500">
                    <x-heroicon-o-arrow-down-left class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pemasukan</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalIncome) }}</h3>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-500">
                    <x-heroicon-o-arrow-up-right class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalExpense) }}</h3>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center text-brand-500">
                    <x-heroicon-o-scale class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Arus Kas Bersih</p>
                    <h3 class="text-xl font-bold {{ $netFlow >= 0 ? 'text-brand-500' : 'text-red-500' }}">{{ $netFlow < 0 ? '-' : '' }}{{ $formatRupiah(abs($netFlow)) }}</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Expenses by Category -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 lg:col-span-1">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Pengeluaran Berdasarkan Kategori</h2>
                <div class="space-y-4">
                    @forelse($expensesByCategory as $expense)
                    @php
                        $percentage = $totalExpense > 0 ? round(($expense['total'] / $totalExpense) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $expense['color'] }}"></div> {{ $expense['name'] }}
                            </span>
                            <span class="text-gray-900 dark:text-white font-semibold">{{ $formatRupiah($expense['total']) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 flex items-center justify-between relative">
                            <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $expense['color'] }}"></div>
                        </div>
                        <p class="text-xs text-gray-500 text-right mt-1">{{ $percentage }}%</p>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                            <x-heroicon-o-chart-pie class="w-6 h-6 text-gray-400" />
                        </div>
                        <p class="text-sm text-gray-500">Tidak ada pengeluaran.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 lg:col-span-2 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Rincian Transaksi</h2>
                    <span class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">{{ $transactions->count() }} Transaksi</span>
                </div>
                <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 sticky top-0 z-10">
                            <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="p-4">Tanggal</th>
                                <th class="p-4">Kategori & Deskripsi</th>
                                <th class="p-4">Tipe</th>
                                <th class="p-4 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($transactions as $t)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="p-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($t->date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ $t->category->name ?? 'Lainnya' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]">{{ $t->description }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $t->type == 'income' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400' }}">
                                        {{ $t->type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-right font-bold {{ $t->type == 'income' ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }} whitespace-nowrap">
                                    {{ $t->type == 'income' ? '+' : '-' }}{{ $formatRupiah($t->amount) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-brand-50 dark:bg-brand-500/10 rounded-full flex items-center justify-center mb-4 text-brand-500 dark:text-brand-400">
                                            <x-heroicon-o-document-magnifying-glass class="w-8 h-8" />
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Tidak ada data</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">Coba sesuaikan filter rentang tanggal Anda.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
