<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'cost_center_id',
        'title',
        'period_start',
        'period_end',
        'status',
        'total'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function expenses()
    {
        return $this->belongsToMany(Expense::class, 'report_expenses');
    }
    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
}
