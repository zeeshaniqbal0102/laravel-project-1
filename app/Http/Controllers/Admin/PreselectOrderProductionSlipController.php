<?php

namespace App\Http\Controllers\Admin;

use App\DB\PreselectOrder;
use Carbon\Carbon;
use Stripe\Product;
use App\DB\ProductType;
use App\Helpers\MyHelper;
use App\DB\ProductionSlipSearch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Lang;


class PreselectOrderProductionSlipController extends Controller
{
    public function index()
    {   
        $search = ProductionSlipSearch::where('user_id', auth()->id())
            ->where('order_type', 'preselect')
            ->first();
        if ($search && $search->product_type && $search->product_type != 'null') 
            $search->product_type = json_decode($search->product_type, true);
        $role = auth()->user()->get_user_role_name();
        if ($role === 'vendor') {
            $productTypes = ProductType::forVendors(auth()->user());
        } else {
            $productTypes = ProductType::exceptSelects()->get(['id', 'name']);
        }
        return view('admin.production-slip.preselect-order.index',[
            'productTypes' => $productTypes,
            'search' => $search
        ]);
    }

    public function getdata()
    {   
        $searchRequest = request()->only(
            'status',
            'country',
            'product_type',
            'todays_order',
            'created_daterange',
            'ship_daterange'
        );
        $searchRequest['order_date'] = request()->get('created_daterange');
        $searchRequest['ship_date'] = request()->get('ship_daterange');
        $search = request('search');
        $searchRequest['search'] = $search['value'];
        $searchRequest['product_type'] = json_encode($searchRequest['product_type']);
        $searchRequest['order_type'] = 'preselect';
        array_splice($searchRequest, 4, 2); 
        ProductionSlipSearch::updateOrCreate(
            ['user_id' => auth()->user()->id, 'order_type' => 'preselect'],
            $searchRequest
        );

        $role = auth()->user()->get_user_role_name();
        if ($role === 'vendor') {
            $productTypes = ProductType::forVendors(auth()->user());
        } else {
            $productTypes = ProductType::exceptSelects()->get(['id', 'name']);
        }
        $productTypeIds = $productTypes->pluck('id')->toArray();
        $preselectOrders = PreselectOrder::query()
            ->select(
                'preselect_orders.id',
                'preselect_orders.is_printed',
                'preselect_orders.receiver_country',
                'preselect_orders.receiver_firstname',
                'preselect_orders.receiver_lastname',
                'preselect_orders.shipped_date',
                'preselect_orders.quantity',
                'preselect_orders.created_at',
                'preselect_orders.is_pdf_generated',
                'preselect_orders.internal_notes',
                'product_type.name',
                'buyer_products.name as buyer_product',
                'inserts.name as insert_name',
                'preselect_orders.requested_ship_date'
            )
            ->leftJoin('buyer_products', 'preselect_orders.size', '=', 'buyer_products.id')
            ->leftJoin('inserts', 'inserts.id', '=', 'preselect_orders.insert_id')
            ->leftJoin('product_type', 'buyer_products.product_type', '=', 'product_type.id')
        ->when(!empty($productTypeIds), function($query) use($productTypeIds) {
            return $query->whereIn('product_type.id', $productTypeIds);
        });
        $datatables = app('datatables')->of($preselectOrders)
            ->addColumn('action', function ($order) {
                $route = route('admin.production-slip.preselect-orders.regenerate', ['id' => $order->id]);
                $regenerateLink = '<a href="'. $route . '" 
                        class="btn btn-sm btn-primary">Regen</a>';
                $notesButton = '<button class="btn btn-sm btn-primary edit-internal-notes" 
                    data-toggle="modal" 
                    data-target="#notesModal"
                    data-id="' . $order->id . '"
                    data-internal_notes="' . $order->internal_notes . '">Notes</button>';
                if ($order->is_pdf_generated) {
                    return '<a href="' . route('preselect-orders.pdf', ['id' => $order->id]) . '" 
                        class="btn btn-sm btn-primary btn-print-slip" target="_blank">Print</a> '
                        . $regenerateLink . $notesButton;
                } else {
                    return '<a href="#" class="btn btn-sm btn-primary" disabled>Print</a> ' 
                        . $regenerateLink . $notesButton;
                }
            })
            ->editColumn('insert_name', function($order) {
                return $order->insert_name;
            })
            ->editColumn('size', function($order) {
                return $order->buyer_product;
            })
            ->addColumn('select_order', function($order) {
                if ($order->is_pdf_generated) {
                    return '<input class="select_order" type="checkbox" name="orders[]['. $order->id .']"
                        value="' . $order->id . '">';
                }
            })
            ->addColumn('receiver_name', function($order) {
                return $order->receiver_firstname . ' ' . $order->receiver_lastname;
            })
            ->editColumn('is_printed', function ($order) {
                return $order->printedStatus();
            })
            ->editColumn('requested_ship_date', function ($order) {
                return $order->requested_ship_date 
                    ? MyHelper::getContainedString(date('n/j/y', strtotime($order->requested_ship_date)))
                    : ($order->created_at ? MyHelper::getContainedString(date('n/j/y', strtotime($order->created_at))) : '-');
            })
            ->editColumn('created_at', function ($order) {
                return MyHelper::getContainedString(date('n/j/y', strtotime($order->created_at)));
            })
            ->editColumn('internal_notes', function ($order) {
                return '<div class="overflow-ellipsis">' . $order->internal_notes . '</div>';
            });
        if ($datatables->request->get('status') != '') {
            $datatables->where('preselect_orders.is_printed', $datatables->request->get('status'));
        }
        if ($datatables->request->get('product_type')) {
            $productTypes = $datatables->request->get('product_type');
            if (!in_array('all', $productTypes)){
                $datatables->whereIn('product_type.id', $productTypes);
            }
        }
        if ($datatables->request->get('country')) {
            $datatables->where('preselect_orders.receiver_country', $datatables->request->get('country'));
        }
        // date range date search for created_daterange
        if ($datatables->request->get('created_daterange')) {
            $created_daterange = explode(' - ', $datatables->request->get('created_daterange'));
            if ($created_daterange[1] == $created_daterange[0]) {
                $datatables->where('preselect_orders.created_at', 'LIKE', '%' . $created_daterange[0] . '%');
            } else {
                $mixedCreatedDateArray = array(
                    $created_daterange[0],
                    $created_daterange[1] . ' 23:59:59'
                );
                $datatables->whereBetween('preselect_orders.created_at', $mixedCreatedDateArray);
            }

        }

        // date range date search for ship_daterange
        if ($datatables->request->get('ship_daterange')) {
            $ship_daterange = explode(' - ', $datatables->request->get('ship_daterange'));
            if ($ship_daterange[1] == $ship_daterange[0]) {
                $datatables->where(function($query) use ($ship_daterange) {
                    $query->where('preselect_orders.requested_ship_date', 'LIKE', '%' . $ship_daterange[0] . '%')
                        ->orWhere('preselect_orders.created_at', 'LIKE', '%' . $ship_daterange[0] . '%');
                });
            } else {
                $mixedshipDateArray = array(
                    $ship_daterange[0],
                    $ship_daterange[1] . ' 23:59:59'
                );
                $datatables->where(function($query) use ($mixedshipDateArray) {
                    $query->whereBetween('preselect_orders.requested_ship_date', $mixedshipDateArray)
                        ->orWhereBetween('preselect_orders.created_at', $mixedshipDateArray);
                });
            }
        }
        if ($datatables->request->get('todays_order') == 1) {
            $datatables->where('preselect_orders.created_at', 'LIKE', '%' . Carbon::today()->toDateTimeString() . '%');
        }
        $datatables->rawColumns(['select_order', 'internal_notes', 'action']);
        return $datatables->make(true);
    }

    public function printSelected()
    {
        try {
            $rawOrders = request('orderData');
            $orderIds = array_flatten($rawOrders);
            $fileName = md5(date('YmdHis')) . '.pdf';
            if(!Storage::exists('public/merged/preselect')) {
                Storage::makeDirectory('public/merged/preselect', 0775, true); //creates directory
            }
            $merger = new Merger;
            foreach ($orderIds as $orderId) {
                $order = PreselectOrder::where('id', $orderId)->select('quantity')->first();
                for($i = 1; $i <= $order->quantity; $i++) {
                    $merger->addFile(public_path('storage/preselect-pdfs/' . $orderId . '_' . $i. '.pdf'));
                }
            }
            $createdPdf = $merger->merge();
            file_put_contents(storage_path('app/public/merged/preselect' . $fileName), $createdPdf);

            PreselectOrder::wherein('id', $orderIds)
                ->update(['is_printed' => 1]);
            return response()->json([
                'status' => true,
                'pdf_url' => asset('storage/merged/preselect' . $fileName)
            ]);
        } catch (\Exception $e) {
            logger()->error('Preselect order Merge Error:'. json_encode($orderIds));
            logger()->error('Preselect order Merge Error: '. $e->getMessage());
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function editNotes(Request $request, $orderID)
    {
        $order = PreselectOrder::find($orderID);
        if($order) {
            $order->internal_notes = $request->internal_notes;
            $order->save();

            return Redirect::back()->with('message',
                Lang::get('response.CUSTOM_MESSAGE_SUCCESS', ['message' => "Internal Note has been updated"]));
        }
        return Redirect::back()->with('message',
            Lang::get('response.CUSTOM_MESSAGE_ALERT', ['message' => "Order not found"]));
    }
}
