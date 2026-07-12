<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name_ar', 'name_en', 'sku', 'description_ar', 'description_en',
        'unit_price', 'tax_percentage', 'unit', 'track_stock', 'stock_quantity', 'is_active',
    ];

    protected $casts = [
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'unit_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
