<?php

namespace App\Http\Controllers\Admin;

use App\DB\Giftcards;
use App\DB\Item;
use App\DB\Product;
use App\DB\ProductType;
use Yajra\Datatables\Datatables;

class CookieOrderReportController
{
    public function index()
    {
        return view('admin.cookies.order-report');
    }

    public function ajaxOrderReport()
    {
        $data = Item::getItemsForOrdersByType(3);

        $datatables = Datatables::of($data)
        ->addColumn('sku', function ($data) {
            if ($data->order_type == 'receiver-order') {
                $giftcard = Giftcards::with('product')->where('card_code', $data->gift_code)->first();
                if ($giftcard && $giftcard->product) {
                    return $giftcard->product->sku;
                }
            } else if ($product = Product::find($data->size)) {
                return $product->sku;
            }
            return '';
        });
        if ($datatables->request->get('created_date')) {
			$datatables->where('items.created_at', 'LIKE', $datatables->request->get('created_date') . '%');
		}
        return $datatables->make(true);
    }
}
