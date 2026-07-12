<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'invoice_logo_path',
        'primary_color',
        'secondary_color',
        'show_qr',
        'show_vat',
        'show_cr',
        'default_tax_percentage',
        'invoice_pdf_language',
    ];

    protected $casts = [
        'show_qr' => 'boolean',
        'show_vat' => 'boolean',
        'show_cr' => 'boolean',
        'default_tax_percentage' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
