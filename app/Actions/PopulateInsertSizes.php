<?php


namespace App\Actions;


use App\DB\Inserts;
use Illuminate\Support\Facades\DB;

class PopulateInsertSizes
{
    public function handle()
    {
        $inserts = Inserts::simplePaginate(500);
        $newSizes = [];
        foreach($inserts as $insert) {
            $sizes = json_decode($insert->size, true);
            if (is_array($sizes)) {
                $preparedData = array_map(function($size) use($insert) {
                    return [
                        'buyer_product_id' => $size,
                        'insert_id' => $insert->id
                    ];
                }, $sizes);
                $newSizes[] = $preparedData;
            } else {
                logger()->warning('Insert error: '. $insert->id);
            }
        }
        $newData = array_merge([], ...$newSizes);
        DB::table('buyer_product_insert')
                ->insert($newData);
        $nextPageUrl = $inserts->nextPageUrl();
        return "<a href='$nextPageUrl'>$nextPageUrl</a>";
    }
}