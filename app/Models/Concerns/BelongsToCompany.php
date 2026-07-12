<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * أي موديل بيستخدم الـ trait ده بيتفلتر تلقائياً بـ company_id بتاع
 * اليوزر المسجل دخوله، وبيتحط تلقائياً وقت الإنشاء.
 * ده بيمنع أي احتمال تسريب بيانات بين الشركات (tenant isolation).
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(
                    $builder->getModel()->getTable().'.company_id',
                    auth()->user()->company_id
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
