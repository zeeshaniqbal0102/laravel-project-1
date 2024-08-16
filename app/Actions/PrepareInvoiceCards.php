<?php

namespace App\Actions;

use Illuminate\Support\Collection;

class PrepareInvoiceCards 
{

    public function handle(Collection $cards)
    {
        return $cards->map(function($card) {
            return $card->card_id;
        })->toArray();
    }

}