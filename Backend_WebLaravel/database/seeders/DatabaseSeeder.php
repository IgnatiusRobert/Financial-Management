<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\SavingsGoal;
use App\Models\Budget;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin1',
            'email' => 'adminabp@abp.com',
            'password' => Hash::make('adminabp123'),
            'role' => 'admin',
        ]);

        // Regular User
        $user = User::create([
            'name' => 'User Test',
            'email' => 'usertest1@abp.com',
            'password' => Hash::make('usertest123'),
            'role' => 'user',
        ]);

        // Default Categories
        Category::create(['name' => 'Food', 'icon' => 'shopping-bag', 'color' => 'red']);
        Category::create(['name' => 'Transport', 'icon' => 'truck', 'color' => 'blue']);
        Category::create(['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => 'emerald']);
        Category::create(['name' => 'Salary', 'icon' => 'banknotes', 'color' => 'teal']);
        Category::create(['name' => 'Savings', 'icon' => 'currency-dollar', 'color' => 'amber']);
    }
}
