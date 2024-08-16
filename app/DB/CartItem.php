<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = "cart";

    protected $fillable = [
        'user_id',
        'company_id',
        'sender_name',
        'receiver_name',
        'receiver_email',
        'product_id',
        'card_sku',
        'mail_delivery_date',
        'message',
        'intl_shipping',
        'delivery_method',
        'discount',
        'purchased',
        'created_at',
        'updated_at',
        'browser_information',
        'ip_address',
        'receiver_first_name',
        'receiver_last_name',
        'size',
        'quantity',
        'locations',
        'insert_id',
        'receiver_phone',
        'receiver_company',
        'receiver_address1',
        'receiver_address2',
        'receiver_city',
        'receiver_zip',
        'receiver_country',
        'receiver_state',
        'requested_ship_date',
        'note',
        'buyer_note',
        'subtotal',
        'corporate_discount',
        'volume_discount',
        'total',
        'occasion',
        'products',
        'type',
        'shipping_method',
        'insert_price',
        'shipping_amount',
        'sender_email',
        'deep_dish',
        'dd_charge',
        'shipping_id',
        'image_path',
        'image_type',
        'intl_shipping_amount',
    ];

    public function design()
    {
        return $this->belongsTo('App\DB\GiftcardImages', 'card_sku', 'sku');
    }

    public function product()
    {
        return $this->belongsTo('App\DB\Product', 'product_id');
    }
    public function getFormattedRequestedShipDate()
    {
        return !empty($this->requested_ship_date)
            ? \MyHelper::customDateFormatterFromMySql($this->requested_ship_date)
            : 'asap';
    }

    public static function prepareCartItemForDisplay($cartItems, $buyerProducts, $productTypes)
    {
        $cartItems->each(function ($item) use ($buyerProducts, $productTypes) {
            $item->product = $buyerProducts->where('id', $item->product_id)->first();
            $item->productCategory = $productTypes->where('id', $item->product->product_type)->first();
            return $item;
        });
        return $cartItems;
    }
}