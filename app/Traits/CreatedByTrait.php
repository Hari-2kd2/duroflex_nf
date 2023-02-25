<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CreatedByTrait
{
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_by = auth()->id();
        });

        self::addGlobalScope(function (Builder $builder) {
            $builder->where('created_by', 1);
        });
    }
}
