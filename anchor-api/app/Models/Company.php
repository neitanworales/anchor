<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Users that belong to this company (membership table: company_users)
     * Pivot stores: role, is_active
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'company_users')
            ->withPivot(['role', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Convenience: only active memberships
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_active', true);
    }

    // Optional relations (if your tables have company_id)
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function costCenters(): HasMany
    {
        return $this->hasMany(CostCenter::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }


}