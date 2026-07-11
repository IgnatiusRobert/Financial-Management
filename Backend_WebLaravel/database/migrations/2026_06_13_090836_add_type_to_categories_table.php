<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('type')->default('expense')->after('name');
        });

        // Insert new categories
        $categories = [
            // Pengeluaran
            ['name' => 'Belanja', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Makanan/Minuman', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'color' => '#f59e0b'],
            ['name' => 'Pakaian', 'type' => 'expense', 'color' => '#eab308'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'color' => '#22c55e'],
            ['name' => 'Anak Anak', 'type' => 'expense', 'color' => '#14b8a6'],
            ['name' => 'Kecantikan', 'type' => 'expense', 'color' => '#3b82f6'],
            ['name' => 'Rokok', 'type' => 'expense', 'color' => '#6366f1'],
            
            // Pemasukkan
            ['name' => 'Gaji', 'type' => 'income', 'color' => '#10b981'],
            ['name' => 'Paruh Waktu', 'type' => 'income', 'color' => '#06b6d4'],
            ['name' => 'Investasi', 'type' => 'income', 'color' => '#8b5cf6'],
            ['name' => 'Bonus', 'type' => 'income', 'color' => '#d946ef'],
            ['name' => 'Lainnya', 'type' => 'income', 'color' => '#64748b'],
        ];

        foreach ($categories as $cat) {
            \Illuminate\Support\Facades\DB::table('categories')->insert($cat);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
