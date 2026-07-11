<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola preferensi akun dan pengaturan aplikasi Anda.</p>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Preferensi Notifikasi</h3>
            
            <div class="space-y-6">
                <!-- Notif Item -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Target Tabungan Tercapai</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Terima notifikasi ketika Anda mencapai target tabungan.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    </label>
                </div>
                
                <!-- Notif Item -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Anggaran Hampir Habis (80%)</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Peringatan ketika pengeluaran mencapai 80% dari anggaran.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    </label>
                </div>

                <!-- Notif Item -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Pengeluaran Melebihi Anggaran</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Notifikasi saat Anda over-budget.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    </label>
                </div>

                <!-- Notif Item -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Pengingat Transaksi Rutin</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ingatkan tentang tagihan dan transaksi berulang.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                    </label>
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-800 my-8">

            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Pengaturan Tampilan</h3>
            
            <div class="space-y-6">
                <!-- Tampilan Item -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Mata Uang Utama</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Semua laporan akan menggunakan mata uang ini.</p>
                    </div>
                    <select disabled class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-brand-500 focus:border-brand-500 block w-full sm:w-48 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-brand-500 dark:focus:border-brand-500">
                        <option selected>Rupiah (Rp)</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button type="button" @click="$dispatch('notify', { type: 'success', message: 'Pengaturan berhasil disimpan!' })" class="bg-brand-500 hover:bg-brand-600 text-white font-medium py-2 px-6 rounded-xl transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
