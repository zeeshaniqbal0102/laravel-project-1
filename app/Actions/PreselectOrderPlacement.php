<?php


namespace App\Actions;


use App\DB\Coupon;
use App\DB\Inserts;
use App\DB\Item;
use App\DB\PreselectOrder;
use App\DB\ReceiverProduct;
use App\Helpers\MyHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PreselectOrderPlacement
{
    public function handle(
        $preselectItems,
        $paymentMethod,
        $candyDropThresholdFeature,
        $couponData
    ) {
        if ($preselectItems->count() == 0) {
            return [];
        }
        $preselectOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold(
            $candyDropThresholdFeature, 'receiver-inventory-drop-level'
        );

        foreach ($preselectItems as $preselectItem) {
            // create preselect order
            $product_ids = explode(',', $preselectItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));
            $preselectCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $preselectItem);
            $preselectOrder = $this->createPreselectOrder($preselectItem, $candies,
            $items_count, $paymentMethod, $preselectCouponDiscount, 1);
            foreach ($product_ids as $item => $count) {
                $receiver_product = ReceiverProduct::where('product_id', $item)->first();
                $itemCount = PreselectOrder::getItemCount($preselectOrder, $count);
                ReceiverProduct::decrementInventory($item, $itemCount);
                $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();
                Item::createForPreselect($preselectOrder, $receiver_product, $itemCount);

                // disable if threshold reached
                $recent_receiver_product->disableIfCanBe($candyDropThreshold);
            }
            $preselectCandies = explode(',', $preselectOrder->candies);
            $preselectOrder->products = ReceiverProduct::whereIn("product_id", $preselectCandies)->get();
            $preselectOrders[] = $preselectOrder->id;
        }
        return $preselectOrders;
    }

    public function handleForCompanyPaidAtMonthEnd(
        $preselectItems,
        $paymentMethod,
        $candyDropThresholdFeature,
        &$updatedCreditAmount,
        $couponData = null,
        $paid = 0,
        $company = null,
        $perOrderCredit = 0.00
    ) {
        if ($preselectItems->count() == 0) {
            return [];
        }
        $preselectOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold($candyDropThresholdFeature, 'receiver-inventory-drop-level');

        foreach ($preselectItems as $preselectItem) {
            // create preselect order
            $product_ids = explode(',', $preselectItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));
            $preselectCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $preselectItem);

            $preselectCredit = $this->applicableCredit($updatedCreditAmount, $preselectItem->total);
            $updatedCreditAmount -= $preselectCredit;
            $preselectItem->corporate_discount = (float) format_number($preselectItem->corporate_discount + $perOrderCredit);

            $preselectOrder = $this->createPreselectOrder($preselectItem, $candies,
                $items_count, $paymentMethod, $preselectCouponDiscount, $paid, $company, $preselectCredit);
            MyHelper::addAdjustments($preselectOrder);
            PreselectOrder::where('id', $preselectOrder->id)->update(['paid' => 1]);
            foreach ($product_ids as $item => $count) {
                $receiver_product = ReceiverProduct::where('product_id', $item)->first();
                $itemCount = PreselectOrder::getItemCount($preselectOrder, $count);
                ReceiverProduct::decrementInventory($item, $itemCount);
                $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();
                Item::createForPreselect($preselectOrder, $receiver_product, $itemCount);

                // disable if threshold reached
                $recent_receiver_product->disableIfCanBe($candyDropThreshold);
            }
            $preselectCandies = explode(',', $preselectOrder->candies);
            $preselectOrder->products = ReceiverProduct::whereIn("product_id", $preselectCandies)->get();

            $preselectOrders[] = $preselectOrder->id;
        }
        return $preselectOrders;
    }

    public function handleForCompanyPaidDirectly(
        $preselectItems,
        $paymentMethod,
        $candyDropThresholdFeature,
        &$updatedCreditAmount,
        $couponData = null,
        $paid = 0,
        $company = null,
        $perOrderCredit = 0.00
    ) {
        if ($preselectItems->count() == 0) {
            return [];
        }
        $preselectOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold(
            $candyDropThresholdFeature,
            'receiver-inventory-drop-level'
        );

        foreach ($preselectItems as $preselectItem) {
            // create preselect order
            $product_ids = explode(',', $preselectItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));
            $preselectCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $preselectItem);

            $preselectCredit = $this->applicableCredit($updatedCreditAmount, $preselectItem->total);
            $updatedCreditAmount -= $preselectCredit;
            $preselectItem->corporate_discount = (float) format_number($preselectItem->corporate_discount + $perOrderCredit);

            $preselectOrder = $this->createPreselectOrder($preselectItem, $candies,
                $items_count, $paymentMethod, $preselectCouponDiscount, $paid, $company, $preselectCredit);
            if ($preselectOrder) {
                foreach ($product_ids as $item => $count) {
                    $receiver_product = ReceiverProduct::where('product_id', $item)->first();

                    $itemCount = PreselectOrder::getItemCount($preselectOrder, $count);
                    ReceiverProduct::decrementInventory($item, $itemCount);
                    $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();

                    Item::createForPreselect($preselectOrder, $receiver_product, $itemCount);

                    // disable if threshold reached
                    $recent_receiver_product->disableIfCanBe($candyDropThreshold);
                }
                $preselectCandies = explode(',', $preselectOrder->candies);
                $preselectOrder->products = ReceiverProduct::whereIn("product_id", $preselectCandies)->get();

                $preselectOrders[] = $preselectOrder->id;
            }

        }
        return $preselectOrders;
    }

    protected function createPreselectOrder(
        $preselectData,
        $candies,
        $items_count,
        $payment_method,
        $couponDiscount,
        $paid = 0,
        $company = null,
        $credit = 0.00
    ) {
        $invoiceByUser = 0;
        if ($company && $company->invoice_by_user && Auth::user() && Auth::user()->isCompanyUser()) {
            $invoiceByUser = 1;
        }
        $couponCode = Session::get('coupon_code');

        $total = $preselectData->subtotal + $preselectData->shipping_amount + $preselectData->intl_shipping_amount - $preselectData->volume_discount;
        if ($couponDiscount > 0.00) {
            $preselectData->corporate_discount = 0.00;
        } else {
            $total -= $preselectData->corporate_discount;
        }
        $total = format_number($total);
        $company_id = empty($preselectData->company_id)
            ? null
            : $preselectData->company_id;
        $preselectOrder = PreselectOrder::create([
            'size' => $preselectData->size,
            'placed_at' => Carbon::now()->toDateTimeString(),
            'status' => 'completed',
            'quantity' => $preselectData->quantity,
            'candies' => $candies,
            'insert_id' => empty($preselectData->insert_id) ? null : $preselectData->insert_id,
            'locations' => $preselectData->locations,
            'receiver_firstname' => $preselectData->receiver_first_name,
            'receiver_lastname' => $preselectData->receiver_last_name,
            'receiver_email' => $preselectData->receiver_email,
            'receiver_phone' => $preselectData->receiver_phone,
            'receiver_company' => $preselectData->receiver_company,
            'receiver_address1' => $preselectData->receiver_address1,
            'receiver_address2' => $preselectData->receiver_address2,
            'receiver_city' => $preselectData->receiver_city,
            'receiver_zip' => $preselectData->receiver_zip,
            'receiver_country' => $preselectData->receiver_country,
            'receiver_state' => $preselectData->receiver_state,
            'requested_ship_date' => $preselectData->requested_ship_date,
            'note' => $preselectData->note,
            'subtotal' => $preselectData->subtotal,
            'corporate_discount' => ($couponDiscount > 0.00) ? 0.00 : $preselectData->corporate_discount,
            'volume_discount' => $preselectData->volume_discount,
            'shipping' => $preselectData->shipping_amount,
            'total' => $total,
            'user_id' => (session()->get('user_id')) ?: Auth::id(),
            'company_id' => $company_id,
            'type' => 'preselect',
            'item_count' => $items_count,
            'buyers_note' => $preselectData->buyer_note,
            'invoice_by_user' => $invoiceByUser,
            'paid' => $paid,
            'coupon_code' => $couponCode,
            'payment_method' => $payment_method,
            'coupon_discount' => $couponDiscount,
            'credit_discount' => $credit,
            'shipping_id' => $preselectData->shipping_id,
            'shipping_method' => $preselectData->shipping_method,
            'insert_total_price' => $preselectData->insertTotalPrice,
            'intl_shipping' => $preselectData->intl_shipping_amount,
        ]);
        $internalNotes = "";
        if ($preselectOrder && $preselectOrder->inserts) {
            $internalNotes = $preselectOrder->inserts->internal_notes;
        }
        $preselectOrder->internal_notes = $internalNotes;
        $preselectOrder->save();
        if ($preselectData->insert_id) {
            Inserts::updateInventoryAndDisable($preselectData->insert_id, $preselectData->quantity);
        }
        return $preselectOrder;
    }

    protected function applicableCredit($credit, $total)
    {
        return $credit < 0.00 || $total <= 0.00 ? 0.00 : min($credit, $total);
    }

}