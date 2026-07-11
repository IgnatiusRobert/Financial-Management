<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Ringkasan Keuangan</h1>
                @php
                    $periodText = 'Bulan Ini';
                    if (($filter ?? 'monthly') == 'daily') $periodText = 'Hari Ini';
                    elseif (($filter ?? 'monthly') == 'weekly') $periodText = 'Minggu Ini';
                    elseif (($filter ?? 'monthly') == 'yearly') $periodText = 'Tahun Ini';
                @endphp
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Performa keuangan Anda pada periode: <span class="font-semibold">{{ $periodText }}</span>.</p>
            </div>
            <div class="flex items-center gap-3">
                <div x-data="{ open: false }" class="relative hidden sm:block">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-3 py-2 shadow-sm text-sm hover:bg-gray-50 transition-colors">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Periode:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-200">
                            @if(($filter ?? 'monthly') == 'daily') Hari Ini 
                            @elseif(($filter ?? 'monthly') == 'weekly') Minggu Ini 
                            @elseif(($filter ?? 'monthly') == 'yearly') Tahun Ini 
                            @else Bulan Ini @endif
                        </span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 ml-2" />
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-lg z-50 overflow-hidden" style="display: none;">
                        <a href="{{ route('dashboard', ['filter' => 'daily']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Hari Ini</a>
                        <a href="{{ route('dashboard', ['filter' => 'weekly']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Minggu Ini</a>
                        <a href="{{ route('dashboard', ['filter' => 'monthly']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Bulan Ini</a>
                        <a href="{{ route('dashboard', ['filter' => 'yearly']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Tahun Ini</a>
                    </div>
                </div>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-2 bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-100 dark:border-brand-500/20 px-4 py-2 rounded-xl shadow-sm hover:bg-brand-100 dark:hover:bg-brand-500/20 transition-colors text-sm font-medium">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Unduh Laporan
                </a>
            </div>
        </div>

        @php
            $formatRupiah = function($angka) {
                return 'Rp ' . number_format($angka, 0, ',', '.');
            };
        @endphp

        <!-- 4 Top Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <!-- Saldo Card -->
            <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl shadow-lg shadow-brand-500/30 p-6 text-white relative overflow-hidden group">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:bg-white/20 transition-all duration-500"></div>
                <div class="flex justify-between items-start mb-4 relative z-10">
                    <div>
                        <p class="text-brand-100 text-sm font-medium">Total Saldo</p>
                        <h3 class="text-2xl font-bold mt-1">{{ $formatRupiah($saldoSaatIni) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        <x-heroicon-o-wallet class="w-5 h-5 text-white" />
                    </div>
                </div>
                <div class="relative z-10 flex items-center gap-1 text-sm font-medium text-brand-100">
                    <x-heroicon-o-check-badge class="w-4 h-4 text-green-300" />
                    <span>Real-time</span>
                </div>
            </div>

            <!-- Pemasukan -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pemasukan</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $formatRupiah($pemasukan) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                        <x-heroicon-o-arrow-down-left class="w-5 h-5 text-green-500" />
                    </div>
                </div>
                <div class="flex items-center gap-1 text-sm font-medium text-green-600 dark:text-green-400">
                    <x-heroicon-o-check-badge class="w-4 h-4" />
                    <span>Total periode ini</span>
                </div>
            </div>

            <!-- Pengeluaran -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pengeluaran</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $formatRupiah($pengeluaran) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center">
                        <x-heroicon-o-arrow-up-right class="w-5 h-5 text-red-500" />
                    </div>
                </div>
                <div class="flex items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                    <span class="text-red-500 font-semibold">{{ $pemasukan > 0 ? round(($pengeluaran/$pemasukan)*100) : 0 }}%</span> dari pemasukan
                </div>
            </div>

            <!-- Tabungan -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Anggaran Terkumpul</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $formatRupiah($tabungan) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-blue-500" />
                    </div>
                </div>
                <div class="flex items-center gap-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                    <x-heroicon-o-check-badge class="w-4 h-4" />
                    <span>Terus tingkatkan!</span>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column (Wider) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- AI Allocation Tips -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Allocation Tips</h2>
                        <a href="{{ route('dashboard', ['refresh_tips' => 1]) }}" class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <x-heroicon-o-arrow-path class="mr-1.5 h-4 w-4" />
                            Refresh tips
                        </a>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-xl border px-4 py-3 text-sm
                            {{ str_starts_with($aiStatus ?? '', 'ai_')
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-500/10 dark:text-emerald-300'
                                : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-600/40 dark:bg-amber-500/10 dark:text-amber-300' }}">
                            @if(($aiStatus ?? '') === 'ai_live')
                                AI aktif: tips baru berhasil dibuat dari data keuangan terbaru Anda.
                            @elseif(($aiStatus ?? '') === 'ai_cached')
                                AI aktif: menampilkan tips cache bulan ini untuk respons lebih cepat.
                            @elseif(($aiStatus ?? '') === 'ai_cached_error')
                                Maaf, AI Groq sedang tidak ada. Menampilkan tips cache terakhir yang masih valid.
                            @else
                                Maaf, AI Groq sedang tidak ada. Menggunakan rule-based tips.
                            @endif
                        </div>

                        @forelse($aiTips as $tip)
                            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-4 py-4">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $tip['title'] }}</p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $tip['text'] }}</p>
                            </div>
                        @empty
                            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-4 py-4">
                                <p class="font-semibold text-gray-900 dark:text-white">Belum ada tips</p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Tambahkan transaksi agar AI bisa memberi saran alokasi yang lebih akurat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Kategori Pengeluaran & Analisis -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pengeluaran Terbesar</h2>
                        <div class="space-y-4">
                            @forelse($topExpenses as $expense)
                            @php
                                $percentage = $pengeluaran > 0 ? round(($expense->total / $pengeluaran) * 100) : 0;
                                $color = $expense->category->color ?? '#f43f5e';
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full" style="background-color: {{ $color }}"></div> {{ $expense->category->name ?? 'Lainnya' }}
                                    </span>
                                    <span class="text-gray-900 dark:text-white font-semibold">{{ $formatRupiah($expense->total) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $color }}"></div>
                                </div>
                            </div>
                            @empty
                            <p class="text-gray-500 text-sm text-center py-4">Belum ada data pengeluaran periode ini.</p>
                            @endforelse
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Health Score Keuangan</h2>
                            <div class="flex items-end gap-3 mb-4">
                                <span class="text-4xl font-bold text-brand-500">{{ round($healthScore) }}</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">/ 100</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @if($healthScore >= 80)
                                    Sangat Baik! Kondisi keuangan Anda sangat sehat. Pertahankan kebiasaan menabung Anda.
                                @elseif($healthScore >= 50)
                                    Cukup Baik. Anda masih memiliki ruang untuk meningkatkan tabungan dan menekan pengeluaran.
                                @else
                                    Perlu Perhatian. Pengeluaran Anda hampir mendekati atau melebihi pemasukan.
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('reports.index') }}" class="text-brand-600 dark:text-brand-400 text-sm font-medium hover:underline flex items-center gap-1 mt-4">
                            Lihat Analisis Lengkap <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Target Tabungan -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Target Anggaran</h2>
                        <a href="{{ route('savings-goals.index') }}" class="w-8 h-8 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center text-brand-600 dark:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-500/20 transition-colors">
                            <x-heroicon-o-plus class="w-5 h-5" />
                        </a>
                    </div>
                    
                    <div class="space-y-5">
                        @forelse($targetTabungan->take(4) as $goal)
                        @php
                            $percent = $goal->target_amount > 0 ? min(100, round(($goal->current_amount / $goal->target_amount) * 100)) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-50 dark:bg-brand-500/10 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                        <x-heroicon-o-star class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $goal->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $formatRupiah($goal->current_amount) }} / {{ $formatRupiah($goal->target_amount) }}</p>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $percent }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                <div class="bg-brand-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada target anggaran.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Transaksi Terakhir -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Transaksi Terakhir</h2>
                        <a href="{{ route('transactions.index') }}" class="text-brand-600 dark:text-brand-400 text-sm font-medium hover:underline">Lihat Semua</a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($recentTransactions as $transaction)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center {{ $transaction->type == 'income' ? 'text-green-500' : 'text-red-500' }} group-hover:bg-gray-100 transition-colors">
                                    @if($transaction->type == 'income')
                                        <x-heroicon-o-arrow-down-left class="w-5 h-5" />
                                    @else
                                        <x-heroicon-o-arrow-up-right class="w-5 h-5" />
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[120px]">{{ $transaction->description }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($transaction->date)->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold {{ $transaction->type == 'income' ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white' }}">
                                {{ $transaction->type == 'income' ? '+' : '-' }}{{ $formatRupiah($transaction->amount) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">Belum ada transaksi periode ini.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
