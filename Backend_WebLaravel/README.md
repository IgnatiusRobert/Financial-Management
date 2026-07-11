# Smart Expense

A full-stack financial management solution featuring a modern Laravel web application (Livewire) 
---

## 🚀 Panduan Menjalankan Sistem

### Bagian 1: Menjalankan Laravel Backend & API

Laravel backend bertindak sebagai penyedia web dashboard sekaligus API server untuk aplikasi Flutter (menggunakan Laravel Sanctum).

#### 1. Setup Awal Laravel
1. Buka terminal di folder Laravel `c:\laragon\www\Manajemen_Keuanganv1`.
2. Install dependensi composer & npm:
   ```bash
   composer install --ignore-platform-reqs
   npm install
   ```
3. Salin file `.env.example` ke `.env` (jika belum ada) dan konfigurasi database MySQL Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=manajemen_keuangan
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Generate key & jalankan migrasi database:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
   *(Seeder otomatis membuat akun user bawaan dan kategori)*.

#### 2. Menjalankan Server Backend


1. Cari tahu IP lokal komputer Anda menggunakan `ipconfig` di command prompt (misal: `192.168.1.10`).
2. Jalankan Laravel server menggunakan host IP tersebut:
   ```bash
   php artisan serve 
   ```
3. Di terminal terpisah, jalankan Vite untuk aset web:
   ```bash
   npm run dev
   ```

---

