<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'period',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
