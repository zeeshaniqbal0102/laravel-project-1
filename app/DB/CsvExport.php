<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class CsvExport extends Model
{
    protected $table = 'csv_exports';

    public $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
