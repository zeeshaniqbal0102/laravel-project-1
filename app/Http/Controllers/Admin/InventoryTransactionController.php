<?php

namespace App\Http\Controllers\Admin;

use App\DB\InventoryTransaction;
use App\DB\ReceiverProduct;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InventoryTransactionController extends Controller
{

    public function edit($id)
    {
        $inventoryTransaction = InventoryTransaction::where('id', $id)->first();
        if (!$inventoryTransaction) {
            return redirect()->back()
                ->with(
                    'message',
                    trans(
                        'response.CUSTOM_MESSAGE_ALERT',
                        ['message' => "Inventory Transaction successfully deleted."]
                    )
                );
        }
        $user = User::find($inventoryTransaction->user_id);
        return view('admin.inventory-report.edit', compact('inventoryTransaction', 'user'));
    }

    public function update(Request $request, $id)
    {
        $inventoryTransaction = InventoryTransaction::where('id', $id)->first();
        if (!$inventoryTransaction) {
            return redirect()->back()
                ->with(
                    'message',
                    trans(
                        'response.CUSTOM_MESSAGE_ALERT',
                        ['message' => "Inventory Transaction successfully deleted."]
                    )
                );
        }
        $this->validate($request, [
            'user_id' => 'required',
            'quantity' => 'required',
            'minute' => 'required'
        ]);
        $oldQty = $inventoryTransaction->quantity;
        $inventoryTransaction->user_id = request('user_id');
        $inventoryTransaction->quantity = request('quantity');
        $inventoryTransaction->time_logged = request('minute');
        if ($inventoryTransaction->isDirty('quantity')) {
            $difference =  $inventoryTransaction->quantity - $oldQty;
            ReceiverProduct::where('product_id', $inventoryTransaction->product_id)
                ->increment('inventory_qty', $difference);
        }
        $inventoryTransaction->save();

        return redirect()->back()
            ->with(
                'message',
                trans(
                    'response.CUSTOM_MESSAGE_SUCCESS',
                    ['message' => "Inventory Transaction successfully updated."]
                )
            );

    }
    public function delete($id)
    {
        $inventoryTransaction = InventoryTransaction::where('id', $id)->first();
        ReceiverProduct::where('product_id', $inventoryTransaction->product_id)
            ->decrement('inventory_qty',$inventoryTransaction->quantity);
        $inventoryTransaction->delete();
        return redirect()->back()
            ->with(
                'message',
                trans(
                    'response.CUSTOM_MESSAGE_SUCCESS',
                    ['message' => "Inventory Transaction successfully deleted."]
                )
            );
    }
}
