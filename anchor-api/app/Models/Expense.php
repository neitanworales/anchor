<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'cost_center_id',
        'expense_date',
        'vendor',
        'currency',
        'amount',
        'tax_iva',
        'payment_method',
        'receipt_type',
        'cfdi_uuid',
        'cfdi_emitter_rfc',
        'cfdi_emitter_name',
        'cfdi_issue_datetime',
        'status'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'cfdi_issue_datetime' => 'datetime',
        'amount' => 'decimal:2',
        'tax_iva' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function files()
    {
        return $this->hasMany(ExpenseFile::class);
    }
    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_expenses');
    }
}
