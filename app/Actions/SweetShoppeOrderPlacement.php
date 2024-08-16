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

class SweetShoppeOrderPlacement
{
    public function handle($sweetShoppeItems, $paymentMethod, $candyDropThresholdFeature, $couponData)
    {
        if ($sweetShoppeItems->count() == 0) {
            return [];
        }
        $sweetShoppeOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold($candyDropThresholdFeature, 'sweet-shoppe-inventory-drop-level');

        foreach ($sweetShoppeItems as $sweetShoppeItem) {
            // create preselect order
            $product_ids = explode(',', $sweetShoppeItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));

            $sweetShoppeCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $sweetShoppeItem);
            $sweetShoppeOrder = $this->createSweetShoppeOrder($sweetShoppeItem, $candies,
                $items_count, $paymentMethod, $sweetShoppeCouponDiscount, 1);
            foreach ($product_ids as $item => $count) {
                $receiver_product = ReceiverProduct::where('product_id', $item)->first();

                // order is deep dish reduce the quantity twice
                $itemCount = PreselectOrder::getItemCount($sweetShoppeOrder, $count);
                ReceiverProduct::decrementInventory($item, $itemCount);
                $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();

                Item::createForSweetShoppe($sweetShoppeOrder, $receiver_product, $itemCount);

                // disable if threshold reached
                $recent_receiver_product->disableIfCanBe($candyDropThreshold);
            }
            $sweetShoppeCandies = explode(',', $sweetShoppeOrder->candies);
            $sweetShoppeOrder->products = ReceiverProduct::whereIn("product_id", $sweetShoppeCandies)
                ->get();
            $sweetShoppeOrders[] = $sweetShoppeOrder->id;
        }
        return $sweetShoppeOrders;
    }

    public function handleForCompanyPaidAtMonthEnd(
        $sweetShoppeItems,
        $paymentMethod,
        $candyDropThresholdFeature,
        &$updatedCreditAmount,
        $couponData = null,
        $paid = 0,
        $company = null,
        $perOrderCredit = 0.00
    )
    {
        if ($sweetShoppeItems->count() == 0) {
            return [];
        }
        $sweetShoppeOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold($candyDropThresholdFeature, 'sweet-shoppe-inventory-drop-level');

        foreach ($sweetShoppeItems as $sweetShoppeItem) {
            // create preselect order
            $product_ids = explode(',', $sweetShoppeItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));

            $sweetShoppeCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $sweetShoppeItem);

            $sweetShoppeCredit = $this->applicableCredit($updatedCreditAmount, $sweetShoppeItem->total);
            $updatedCreditAmount -= $sweetShoppeCredit;
            $sweetShoppeItem->corporate_discount += $perOrderCredit;

            $sweetShoppeOrder = $this->createSweetShoppeOrder($sweetShoppeItem, $candies,
                $items_count, $paymentMethod, $sweetShoppeCouponDiscount, $paid, $company, $sweetShoppeCredit);
            MyHelper::addAdjustments($sweetShoppeOrder);
            PreselectOrder::where('id', $sweetShoppeOrder->id)->update(['paid' => 1]);
            foreach ($product_ids as $item => $count) {
                $receiver_product = ReceiverProduct::where('product_id', $item)->first();

                // order is deep dish reduce the quantity twice
                $itemCount = PreselectOrder::getItemCount($sweetShoppeOrder, $count);
                ReceiverProduct::decrementInventory($item, $itemCount);

                $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();

                Item::createForSweetShoppe($sweetShoppeOrder, $receiver_product, $itemCount);

                // disable if threshold reached
                $recent_receiver_product->disableIfCanBe($candyDropThreshold);
            }
            $sweetShoppeCandies = explode(',', $sweetShoppeOrder->candies);
            $sweetShoppeOrder->products = ReceiverProduct::whereIn("product_id", $sweetShoppeCandies)
                ->get();

            $sweetShoppeOrders[] = $sweetShoppeOrder->id;
        }
        return $sweetShoppeOrders;
    }

    public function handleForCompanyPaidDirectly(
        $sweetShoppeItems,
        $paymentMethod,
        $candyDropThresholdFeature,
        &$updatedCreditAmount,
        $couponData = null,
        $paid = 0,
        $company = null,
        $perOrderCredit = 0.00
    )
    {
        if ($sweetShoppeItems->count() == 0) {
            return [];
        }
        $sweetShoppeOrders = [];
        $candyDropThreshold = MyHelper::getCandyDropThreshold(
            $candyDropThresholdFeature,
            'sweet-shoppe-inventory-drop-level'
        );
        foreach ($sweetShoppeItems as $sweetShoppeItem) {
            // create preselect order
            $product_ids = explode(',', $sweetShoppeItem->products);
            $items_count = count($product_ids);
            if (count($product_ids) > 0) {
                $product_ids = array_count_values($product_ids);
            }
            $candies = implode(",", array_keys($product_ids));

            $sweetShoppeCouponDiscount = Coupon::calculateCouponDiscountIfAvailable($couponData, $sweetShoppeItem);

            $sweetShoppeCredit = $this->applicableCredit($updatedCreditAmount, $sweetShoppeItem->total);
            $updatedCreditAmount -= $sweetShoppeCredit;
            $sweetShoppeItem->corporate_discount += $perOrderCredit;

            $sweetShoppeOrder = $this->createSweetShoppeOrder($sweetShoppeItem, $candies,
                $items_count, $paymentMethod, $sweetShoppeCouponDiscount, $paid, $company, $sweetShoppeCredit);
            foreach ($product_ids as $item => $count) {
                $receiver_product = ReceiverProduct::where('product_id', $item)->first();

                // order is deep dish reduce the quantity twice
                $itemCount = PreselectOrder::getItemCount($sweetShoppeOrder, $count);
                ReceiverProduct::decrementInventory($item, $itemCount);
                $recent_receiver_product = ReceiverProduct::where('product_id', $item)->first();

                Item::createForSweetShoppe($sweetShoppeOrder, $receiver_product, $itemCount);

                // disable if threshold reached
                $recent_receiver_product->disableIfCanBe($candyDropThreshold);
            }
            $sweetShoppeCandies = explode(',', $sweetShoppeOrder->candies);
            $sweetShoppeOrder->products = ReceiverProduct::whereIn("product_id", $sweetShoppeCandies)
                ->get();

            $sweetShoppeOrders[] = $sweetShoppeOrder->id;
        }
        return $sweetShoppeOrders;
    }

    protected function createSweetShoppeOrder(
        $sweetShoppeData,
        $candies,
        $items_count,
        $payment_method,
        $couponDiscount,
        $paid = 0,
        $company = null,
        $credit = 0.00
    )
    {
        $invoiceByUser = 0;
        if ($company && $company->invoice_by_user && Auth::user() && Auth::user()->isCompanyUser()) {
            $invoiceByUser = 1;
        }

        $couponCode = Session::get('coupon_code');

        $total = $sweetShoppeData->subtotal + $sweetShoppeData->shipping_amount + $sweetShoppeData->intl_shipping_amount - $sweetShoppeData->volume_discount;
        if ($couponDiscount > 0.00) {
            $sweetShoppeData->corporate_discount = 0.00;
        } else {
            $total -= $sweetShoppeData->corporate_discount;
        }
        $preselectOrder = PreselectOrder::create([
            'size' => $sweetShoppeData->size,
            'placed_at' => Carbon::now()->toDateTimeString(),
            'status' => 'completed',
            'quantity' => $sweetShoppeData->quantity,
            'candies' => $candies,
            'insert_id' => empty($sweetShoppeData->insert_id) ? null : $sweetShoppeData->insert_id,
            'locations' => $sweetShoppeData->locations,
            'receiver_firstname' => $sweetShoppeData->receiver_first_name,
            'receiver_lastname' => $sweetShoppeData->receiver_last_name,
            'receiver_email' => $sweetShoppeData->receiver_email,
            'receiver_phone' => $sweetShoppeData->receiver_phone,
            'receiver_company' => $sweetShoppeData->receiver_company,
            'receiver_address1' => $sweetShoppeData->receiver_address1,
            'receiver_address2' => $sweetShoppeData->receiver_address2,
            'receiver_city' => $sweetShoppeData->receiver_city,
            'receiver_zip' => $sweetShoppeData->receiver_zip,
            'receiver_country' => $sweetShoppeData->receiver_country,
            'receiver_state' => $sweetShoppeData->receiver_state,
            'requested_ship_date' => $sweetShoppeData->requested_ship_date,
            'note' => $sweetShoppeData->note,
            'subtotal' => $sweetShoppeData->subtotal,
            'corporate_discount' => ($couponDiscount > 0.00) ? 0.00 : $sweetShoppeData->corporate_discount,
            'volume_discount' => $sweetShoppeData->volume_discount,
            'shipping' => $sweetShoppeData->shipping_amount,
            'total' => $total,
            'user_id' => (session()->get('user_id')) ?: Auth::id(),
            'company_id' => $sweetShoppeData->company_id,
            'type' => 'sweet-shoppe',
            'item_count' => $items_count,
            'buyers_note' => $sweetShoppeData->buyer_note,
            'invoice_by_user' => $invoiceByUser,
            'paid' => $paid,
            'shipping_method' => $sweetShoppeData->shipping_method,
            'coupon_code' => $couponCode,
            'payment_method' => $payment_method,
            'coupon_discount' => $couponDiscount,
            'deep_dish' => $sweetShoppeData->deep_dish,
            'dd_charge' => $sweetShoppeData->dd_charge,
            'credit_discount' => $credit,
            'shipping_id' => $sweetShoppeData->shipping_id,
            'insert_total_price' => $sweetShoppeData->insertTotalPrice,
            'intl_shipping' => $sweetShoppeData->intl_shipping_amount,
        ]);
        $internalNotes = "";
        if ($preselectOrder && $preselectOrder->inserts) {
            $internalNotes = $preselectOrder->inserts->internal_notes;
        }
        $preselectOrder->internal_notes = $internalNotes;
        $preselectOrder->save();
        if ($sweetShoppeData->insert_id) {
            Inserts::updateInventoryAndDisable($sweetShoppeData->insert_id, $sweetShoppeData->quantity);
        }
        return $preselectOrder;
    }

    protected function applicableCredit($credit, $total)
    {
        return $credit < 0.00 || $total <= 0.00 ? 0.00 : min($credit, $total);
    }
}