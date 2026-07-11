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

    <form wire:submit="save" class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-wrap gap-4 items-end">
        
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
            <input type="text" list="kategoriTabungan" wire:model="name" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500" required placeholder="Contoh: Dana Darurat">
            <datalist id="kategoriTabungan">
                <option value="Dana Darurat">
                <option value="Dana Pendidikan">
                <option value="Dana Liburan">
                <option value="Dana Pensiun">
                <option value="Dana Pernikahan">
                <option value="Dana Investasi">
            </datalist>
        </div>
        
        <div class="flex-1 min-w-[150px] relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target (Rp)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                </div>
                <input type="number" step="1" wire:model="target_amount" class="w-full pl-9 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500" required>
            </div>
        </div>

        <div class="flex-1 min-w-[150px] relative">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Terkumpul (Rp)</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                </div>
                <input type="number" step="1" wire:model="current_amount" class="w-full pl-9 rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
        </div>

        <div class="flex-1 min-w-[150px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tenggat Waktu</label>
            <input type="date" wire:model="target_date" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500">
        </div>

        <button type="submit" class="w-full md:w-auto bg-brand-500 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-brand-600 transition-colors shadow-sm flex items-center justify-center gap-2" wire:loading.attr="disabled">
            <x-heroicon-o-plus wire:loading.remove wire:target="save" class="w-5 h-5" />
            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ $editingGoalId ? 'Simpan Perubahan' : 'Buat Target' }}</span>
        </button>

        @if($editingGoalId)
            <button type="button" wire:click="cancelEdit" class="w-full md:w-auto bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-xl font-medium hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                Batal Edit
            </button>
        @endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
        
        <!-- Loading Overlay -->
        <div wire:loading.delay wire:target="delete" class="absolute inset-0 bg-white/50 dark:bg-brand-dark/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-2xl">
            <div class="flex items-center gap-2 text-brand-600 dark:text-brand-400">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="font-medium">Memproses...</span>
            </div>
        </div>

        @forelse($goals as $g)
        @php $percent = $g->target_amount > 0 ? min(100, round(($g->current_amount / $g->target_amount) * 100)) : 0; @endphp
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm relative group hover:shadow-md transition-shadow">
            <div class="absolute top-4 right-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-all">
                <button wire:click="edit({{ $g->id }})" class="p-2 rounded-lg text-gray-400 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-500/10">
                    <x-heroicon-o-pencil-square class="w-5 h-5" />
                </button>
                <button wire:click="delete({{ $g->id }})" wire:confirm="Yakin ingin menghapus target anggaran ini?" class="p-2 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                    <x-heroicon-o-trash class="w-5 h-5" />
                </button>
            </div>
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center border border-brand-100 dark:border-brand-500/20">
                    @php
                        $icon = 'banknotes';
                        if(str_contains(strtolower($g->name), 'darurat')) $icon = 'shield-check';
                        if(str_contains(strtolower($g->name), 'liburan')) $icon = 'globe-alt';
                        if(str_contains(strtolower($g->name), 'pendidikan')) $icon = 'academic-cap';
                        if(str_contains(strtolower($g->name), 'pensiun')) $icon = 'home-modern';
                        if(str_contains(strtolower($g->name), 'investasi')) $icon = 'chart-bar';
                        if(str_contains(strtolower($g->name), 'nikah') || str_contains(strtolower($g->name), 'pernikahan')) $icon = 'heart';
                    @endphp
                    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-lg">{{ $g->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <x-heroicon-o-calendar class="w-4 h-4" />
                        {{ $g->target_date ? \Carbon\Carbon::parse($g->target_date)->translatedFormat("d M Y") : "Tanpa tenggat waktu" }}
                    </p>
                </div>
            </div>
            
            <div class="mb-3 flex justify-between text-sm">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-0.5">Terkumpul</p>
                    <span class="font-bold text-gray-900 dark:text-white text-lg">Rp {{ number_format($g->current_amount, 0, ',', '.') }}</span>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider mb-0.5">Target</p>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Rp {{ number_format($g->target_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 mb-2 overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="{{ $percent >= 100 ? 'bg-green-500' : 'bg-brand-500' }} h-full rounded-full transition-all duration-1000 ease-out relative" style="width: {{ $percent }}%">
                    @if($percent > 0)
                        <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 1rem 1rem;"></div>
                    @endif
                </div>
            </div>
            <div class="text-right text-xs font-semibold {{ $percent >= 100 ? 'text-green-600 dark:text-green-400' : 'text-brand-600 dark:text-brand-400' }}">
                @if($percent >= 100)
                    Target Tercapai! ({{ $percent }}%)
                @else
                    {{ $percent }}% tercapai
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 flex flex-col items-center justify-center text-center bg-white dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl">
            <div class="w-20 h-20 bg-brand-50 dark:bg-brand-500/10 rounded-full flex items-center justify-center mb-4">
                <x-heroicon-o-sparkles class="w-10 h-10 text-brand-500 dark:text-brand-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum ada target anggaran</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md">Mulai rencanakan masa depan keuangan Anda. Buat target anggaran seperti Dana Darurat atau Liburan untuk mulai melacak progres Anda.</p>
        </div>
        @endforelse
    </div>
</div>
