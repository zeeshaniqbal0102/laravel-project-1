<?php

namespace App\Console\Commands;

use App\DB\CartItem;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldCartItems extends Command
{
    protected $signature = 'delete:old-cart-items';

    protected $description = 'Deletes old cart items';

    public function handle()
    {
        $itemsDeleted = CartItem::where('created_at', '<', Carbon::now()->subWeeks(2)->toDateTimeString())
            ->where(function ($query) {
                $query->where('mail_delivery_date', '<=', Carbon::now()->subDay()->toDateString())
                    ->orWhere('type', '!=', 'consumer');
            })
            ->delete();
        $this->info("{$itemsDeleted} Cart Items Deleted");
    }
}
