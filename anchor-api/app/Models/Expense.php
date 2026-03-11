<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
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

        'cfdi_type',
        'cfdi_uuid',
        'cfdi_emitter_rfc',
        'cfdi_emitter_name',
        'cfdi_receiver_rfc',
        'cfdi_currency',
        'cfdi_subtotal',
        'cfdi_total',
        'cfdi_issue_datetime',

        'status',
        'xml_uploaded',
        'xml_original_name',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'cfdi_issue_datetime' => 'datetime',
        'amount' => 'decimal:2',
        'tax_iva' => 'decimal:2',
        'cfdi_subtotal' => 'decimal:2',
        'cfdi_total' => 'decimal:2',
        'xml_uploaded' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function files()
    {
        return $this->hasMany(ExpenseFile::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
