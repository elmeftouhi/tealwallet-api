<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'expense_category_id',
        'user_id',
        'description',
        'expense_date',
        'amount',
    ];

    public function category(){
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id', 'id');
    }
}
