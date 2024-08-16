<?php

namespace App\Http\Controllers;

use App\DB\Giftcards;
use App\DB\Order;
use App\DB\Product;
use App\Helpers\MyHelper;
use Illuminate\Http\Request;
use Log;
use XMLWriter;

class CookieShipstationController extends Controller
{
    public function shipping_endpoint(Request $request)
    {
        if($request->isMethod('POST')) {
            $rawData = file_get_contents('php://input');
            $rawXmlData = simplexml_load_string($rawData);
            $jsonData = json_encode($rawXmlData);
            $xmlFinalData = json_decode($jsonData,TRUE);
            $order_id = isset($xmlFinalData['OrderID']) ?  $xmlFinalData['OrderID'] : '';
            $tracking_number = isset($xmlFinalData['TrackingNumber']) ? $xmlFinalData['TrackingNumber'] : '';
            $carrier = isset($xmlFinalData['Carrier']) ? $xmlFinalData['Carrier'] : '';
            $service = isset($xmlFinalData['Service']) ? $xmlFinalData['Service'] : '';
            $shipdate = isset($xmlFinalData['ShipDate']) ? $xmlFinalData['ShipDate'] : '';
            if ($shipdate) {
                $shipdate = \DateTime::createFromFormat('m/d/Y', $shipdate);
                $shipdate->setTimezone(new \DateTimeZone('America/Denver'));
                $shipdate = $shipdate->format('Y-m-d 00:00:00');
            }
            $order = Order::where('order_id' , $order_id)->first();
            if ($order) {
                $order->carrier = $carrier;
                $order->service = $service;
                $order->tracking_number = $tracking_number;
                if($shipdate) {
                    $order->shipping_created_at = $shipdate;
                }
                $order->is_imported = 1;
                $order->save();
            }
            Log::info('Shipped Order: '. $order_id. ' Tracking Number: ' .$tracking_number);
            return response('Tracking Information Retrieved Successfully',200);

        } else {
            $action = $request->action;
            $start_date = MyHelper::customDateFormatterForShippingDates(urldecode($request->start_date));
            $end_date = MyHelper::customDateFormatterForShippingDates(urldecode($request->end_date));
            $receiverOrders = null;
            if ($action == 'export' && !empty($start_date) && !empty($end_date)) {
                $receiverOrders = Order::where('store_id', 2)
                    ->where(function($query) {
                        $query->whereHas('receiverCard', function ($query) {
                            $query->where('product_type', 3);
                        })->orWhere('product_type', 3);
                    })
                    ->whereBetween('updated_at', [$start_date, $end_date])
                    ->with('items')
                    ->paginate(100);
            }

            if ($receiverOrders) {
                $total_pages = $receiverOrders->lastPage();
                $xml = new XMLWriter();
                $xml->openMemory();
                $xml->startDocument();
                // Orders Start
                $xml->startElement('Orders');
                $xml->writeAttribute('pages', $total_pages);
                foreach ($receiverOrders as $order) {
                    $sw_sku = '';
                    $gift_card = Giftcards::where('card_code', $order->gift_code)->first();

                    $customField2 = ($gift_card) ? $order->product_sku . '   ' . $gift_card->card_code : $order->product_sku;
                    $customField3 = ($gift_card && $gift_card->image) 
                        ? $gift_card->image->sku 
                        : '';
                    $xml->startElement('Order');
                    // Order Id Start
                    $xml->startElement("OrderID");
                    $xml->writeCData($order->order_id);
                    $xml->endElement();
                    // Order Id End
                    // Order Number Start
                    $xml->startElement("OrderNumber");
                    $xml->writeCData($order->order_id);
                    $xml->endElement();
                    // Order Number End
                    // Order Date Start
                    $xml->writeElement("OrderDate", MyHelper::changeDatetoUTC($order->created_at));
                    // Order Date End
                    // Order Status Start
                    $xml->startElement('OrderStatus');
                    $xml->writeCData($order->status);
                    $xml->endElement();
                    // Order Status End
                    // Last Modified Start
                    $xml->writeElement("LastModified",
                        MyHelper::changeDateToUTC($order->updated_at));
                    // Last Modified End
                    // Shipping Mentod Start
                    $xml->startElement('ShippingMethod');
                    $xml->writeCData($order->shipping_method);
                    $xml->endElement();
                    // Shipping Method End
                    // Payment Method Start
                    $xml->startElement('PaymentMethod');
                    $xml->writeCData($order->second_payment_method);
                    $xml->endElement();
                    // Payment Method End
                    // Order Total Start
                    $xml->writeElement("OrderTotal", $gift_card ? (float) $gift_card->final_amount: 0.00);
                    // Order Total End
                    // Tax Amount Start
                    $xml->writeElement("TaxAmount", 0.00);
                    // Tax Amount End
                    // Shipping amount start
                    $xml->writeElement("ShippingAmount", 0.00);
                    // Shipping Amount End
                    // Customer Note Start
                    $xml->startElement('CustomerNotes');
                    $xml->writeCData($order->onestepcheckout_order_comment);
                    $xml->endElement();
                    // Customer Note End
                    // Internal Notes Start
                    $xml->writeElement('InternalNotes', '');
                    // Internal Notes End
                    // Gift
                    $xml->writeElement("Gift", 'false');
                    // Gift Message
                    $xml->writeElement("GiftMessage", '');
                    // Custom field 1
                    $buyerProduct = $order->buyerProductForShipstation($gift_card);
                    $xml->startElement('CustomField1');
                    $xml->writeCData(isset($buyerProduct) ? $buyerProduct->name : '');
                    $xml->endElement();
                    // Custom Field 2
                    $xml->startElement('CustomField2');
                    $xml->writeCData($customField2);
                    $xml->endElement();
                    // Custom Field 3
                    $xml->startElement("CustomField3");
                    $xml->writeCData($customField3);
                    $xml->endElement();
                    // Customer Start
                    $xml->startElement('Customer');
                    // Customer Code Start
                    $xml->startElement('CustomerCode');
                    $xml->writeCData($order->customer_email);
                    $xml->endElement();
                    // Bill To Start
                    $xml->startElement('BillTo');
                    // Name Start
                    $xml->startElement('Name');
                    $xml->writeCData($order->billing_name);
                    $xml->endElement();
                    // Name End
                    // Company Start
                    $xml->startElement('Company');
                    $shipping_company = ('personal' == strtolower($order->shipping_company)) ? '' : $order->shipping_company;
                    $xml->writeCData($shipping_company);
                    $xml->endElement();
                    // Company End
                    // Phone Start
                    $xml->startElement('Phone');
                    $xml->writeCData($order->telephone);
                    $xml->endElement();
                    // Phone End
                    // Email Start
                    $xml->startElement('Email');
                    $xml->writeCData($order->customer_email);
                    $xml->endElement();
                    // Email End
                    $xml->endElement();
                    // Bill To End
                    // Ship To Start
                    $xml->startElement('ShipTo');
                    // Name Start
                    $xml->startElement('Name');
                    $xml->writeCData($order->shipping_name);
                    $xml->endElement();
                    // Name End
                    // Company Start
                    $xml->startElement('Company');
                    $xml->writeCData($shipping_company);
                    $xml->endElement();
                    // Company End
                    // Address 1 Start
                    $xml->startElement('Address1');
                    $xml->writeCData($order->shipping_address);
                    $xml->endElement();
                    // Address 1 End
                    // Address 2 Start
                    $xml->startElement('Address2');
                    $xml->writeCData($order->shipping_address2);
                    $xml->endElement();
                    // Address 2 End
                    // City Start
                    $xml->startElement('City');
                    $xml->writeCData($order->shipping_city);
                    $xml->endElement();
                    // City End
                    // Store Start
                    $xml->startElement('State');
                    $xml->writeCData($order->shipping_state);
                    $xml->endElement();
                    // Store End
                    // Postal Code Start
                    $xml->startElement('PostalCode');
                    $xml->writeCData($order->postcode);
                    $xml->endElement();
                    // Postal Code End
                    // Country Start
                    $xml->startElement('Country');
                    $xml->writeCData($order->shipping_country);
                    $xml->endElement();
                    // Country End
                    // Phone Start
                    $xml->startElement('Phone');
                    $xml->writeCData($order->shipping_telephone);
                    $xml->endElement();
                    // Phone End
                    $xml->endElement();
                    // Send To End
                    $xml->endElement();
                    // Customer End
                    if ($order->items) {
                        // Item Section Start
                        $xml->startElement('Items');
                        foreach ($order->items as $item) {
                            // Individual Item Start
                            $xml->startElement('Item');
                            // Product Sku Start
                            $xml->startElement('SKU');
                            $xml->writeCData($item->product_sku);
                            $xml->endElement();
                            // Product Sku End
                            // Product Name Start
                            $xml->startElement('Name');
                            $xml->writeCData($item->product_name);
                            $xml->endElement();
                            // Product Name End
                            // Quantity Start
                            $xml->writeElement('Quantity', $item->qty_ordered);
                            // Quantity End
                            // UnitPrice Start
                            $xml->writeElement('UnitPrice', 0.00);
                            // Unit Price End
                            // Adjustment
                            $xml->writeElement('Adjustment', 'false');
                            // Adjustment End
                            $xml->endElement();
                            // Individual Item End
                        }
                        $xml->endElement();
                        // Items Section End
                    }
                    $xml->endElement();
                    // Individual Order End

                }
                $xml->endElement();
                // Orders End
                $xml->endDocument();

                $content = $xml->outputMemory();
                $xml = null;

                return response($content)->header('Content-Type', 'text/xml');
            }
        }
    }
}
