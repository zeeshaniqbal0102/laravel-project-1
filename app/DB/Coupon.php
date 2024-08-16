<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'promo_codes';

    protected $fillable = [
        'name',
        'code',
        'message',
        'start_date',
        'end_date',
        'shipping_start_date',
        'shipping_end_date',
        'use_type',
        'product_exclusions',
        'status',
        'discount',
        'discount_type',
        'maximum_discount',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'maximum_discount' => 'float',
    ];

    public static function percentageDiscount($coupon, $amount)
    {
        $percentageDiscount = $coupon->discount * $amount * 0.01;
        if ($coupon->maximum_discount <= 0.00) {
            return $percentageDiscount;
        }
        return min($coupon->maximum_discount, $percentageDiscount);
    }

    public static function calculateCouponDiscountIfAvailable($couponData, $item)
    {
        if (!$couponData) {
            return 0.00;
        }
        $couponDiscount = 0.00;
        $subTotal = $item->subtotal;

        $insert = Inserts::where('id', $item->insert_id)
            ->where('price', '>', 0)
            ->first();
        if ($insert) {
            $subTotal += $insert->price;
        }

        $product_exclusions_arr = explode(',', $couponData->product_exclusions);
        if (in_array($item->product_id, $product_exclusions_arr)) {
            return $couponDiscount;
        }
        if (!$couponData->shipping_start_date || !$couponData->shipping_end_date) {
            if ($couponData->discount_type == 'percentage') {
                return format_number(self::percentageDiscount($couponData, $subTotal));
            } else if ($couponData->discount_type == 'amount') {
                return actual_discount($couponData->discount, $subTotal);
            }
            return $couponDiscount;
        }
        $datetocheck = $item->mail_delivery_date;
        $datetocheck1 = $item->requested_ship_date;

        if ($couponData->shipping_start_date <= $datetocheck && $couponData->shipping_end_date >= $datetocheck) {
            if ($couponData->discount_type == 'percentage') {
                return format_number(self::percentageDiscount($couponData, $subTotal));
            } elseif ($couponData->discount_type == 'amount') {
                return actual_discount($couponData->discount, $subTotal);
            }
        } elseif ($couponData->shipping_start_date <= $datetocheck1 && $couponData->shipping_end_date >= $datetocheck1) {
            if ($couponData->discount_type == 'percentage') {
                return format_number(self::percentageDiscount($couponData, $subTotal));
            } elseif ($couponData->discount_type == 'amount') {
                return actual_discount($couponData->discount, $subTotal);
            }
        }
        return $couponDiscount;
    }
}
