<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyZatcaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'environment',
        'otp',
        'client_id',
        'client_secret',
        'csr',
        'private_key',
        'certificate',
        'production_certificate',
        'secret_key',
        'binary_security_token',
        'request_id',
        'compliance_request_id',
        'compliance_status',
        'access_token',
        'refresh_token',
        'certificate_expiry_date',
        'onboarding_stage',
        'last_synced_at',
    ];

    protected $casts = [
        'certificate_expiry_date' => 'datetime',
        'last_synced_at' => 'datetime',
        // الحقول دي بتتشفر/تتفك تلقائياً - Laravel's 'encrypted' cast بيستخدم APP_KEY
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
        'csr' => 'encrypted',
        'private_key' => 'encrypted',
        'certificate' => 'encrypted',
        'production_certificate' => 'encrypted',
        'secret_key' => 'encrypted',
        'binary_security_token' => 'encrypted',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    protected $hidden = [
        'otp', 'client_secret', 'private_key', 'secret_key',
        'binary_security_token', 'access_token', 'refresh_token',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isProductionReady(): bool
    {
        return $this->environment === 'production' && $this->compliance_status === 'passed';
    }
}
