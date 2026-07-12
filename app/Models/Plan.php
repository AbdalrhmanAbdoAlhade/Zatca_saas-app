<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_invoices',
        'zatca_integration',
        'reports_access',
        'features',
        'is_active',
    ];

    protected $casts = [
        'zatca_integration' => 'boolean',
        'reports_access' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
