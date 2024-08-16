<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "category";
    protected $fillable = [
        "name",
        "description",
        "start_date",
        "end_date",
        "sort_key",
        "category_image",
        "healthy",
        "product_type",
    ];

    protected $guarded = [
        "id",
        "created_at",
        "updated_at"
    ];

    public function getSlugAttribute()
    {
        return str_slug($this->name);
    }

    public function products()
    {
        return $this->hasMany('App\DB\ReceiverProduct', 'category');
    }

    public function productTypes()
    {
        return $this->belongsTo(ProductType::class, 'product_type', 'id');
    }

    public function scopeHealthyOnly($query)
    {
        return $query->whereHealthy(1);
    }

    public function scopeNonExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', date('Y-m-d'));
        })
        ->where(function ($query) {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>=', date('Y-m-d'));
        });
    }

    public function scopeProductType($query, $productType)
    {
        return $query->where('product_type', $productType);
    }
}
