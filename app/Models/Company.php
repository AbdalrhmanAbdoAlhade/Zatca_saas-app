<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'trade_name_ar',
        'trade_name_en',
        'owner_name',
        'vat_number',
        'commercial_registration_number',
        'tax_certificate_number',
        'country',
        'city',
        'district',
        'street',
        'building_number',
        'additional_number',
        'postal_code',
        'logo_path',
        'identity_image_path',
        'tax_certificate_file_path',
        'commercial_registration_file_path',
        'status',
        'default_locale',
        'owner_user_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function zatcaSettings(): HasOne
    {
        return $this->hasOne(CompanyZatcaSetting::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
