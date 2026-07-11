<div class="space-y-6 relative">
    
    <!-- Toast via Livewire -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-4 right-4 z-50 pointer-events-auto px-4 py-3 rounded-xl shadow-lg border border-green-100 dark:border-green-900/30 bg-white dark:bg-gray-800 text-gray-900 dark:text-white flex items-center gap-3 min-w-[300px]">
            <div class="text-green-500">
                <x-heroicon-o-check-circle class="w-6 h-6" />
            </div>
            <p class="text-sm font-medium">{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full md:w-auto">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
            <select wire:model="category_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500" required>
                <option value="">Pilih Kategori</option>
                @foreach($categories as $c)
                    <option value="{{$c->id}}">{{$c->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 w-full md:w-auto relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Batas Anggaran (Rp)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                </div>
                <input type="number" step="1" wire:model="amount" class="w-full pl-9 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500" required>
            </div>
        </div>
        <div class="flex-1 w-full md:w-auto">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
            <select wire:model="period" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="monthly">Bulanan</option>
                <option value="yearly">Tahunan</option>
            </select>
        </div>
        <button type="submit" class="w-full md:w-auto bg-brand-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm flex items-center justify-center gap-2" wire:loading.attr="disabled">
            <x-heroicon-o-plus wire:loading.remove wire:target="save" class="w-5 h-5" />
            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>Simpan</span>
        </button>
    </form>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden relative">
        
        <!-- Loading Overlay -->
        <div wire:loading.delay wire:target="delete" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="flex items-center gap-2 text-brand-600 dark:text-brand-400">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="font-medium">Memproses...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <tr class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Periode</th>
                        <th class="p-4 text-right">Batas Anggaran</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($budgets as $b)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <td class="p-4 text-sm font-medium text-gray-900 dark:text-white">
                            <span class="inline-flex items-center gap-1.5">
                                <div class="w-2 h-2 rounded-full" style="background-color: {{ $b->category->color ?? '#9ca3af' }}"></div>
                                {{ $b->category->name ?? "-" }}
                            </span>
                        </td>
                        <td class="p-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $b->period == 'monthly' ? 'Bulanan' : 'Tahunan' }}
                        </td>
                        <td class="p-4 text-sm text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($b->amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center text-sm">
                            <button wire:click="delete({{ $b->id }})" wire:confirm="Yakin ingin menghapus anggaran ini?" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-brand-50 dark:bg-brand-500/10 rounded-full flex items-center justify-center mb-4 text-brand-500 dark:text-brand-400">
                                    <x-heroicon-o-chart-pie class="w-8 h-8" />
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Belum ada anggaran</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">Tentukan batas pengeluaran untuk setiap kategori agar keuangan Anda lebih terkontrol.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
