<?php

namespace App\Http\Controllers\Admin;

use App\DB\ReferalUsage;
use App\Http\Controllers\Controller;
use App\User;

class ReferalController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function referalCodes()
    {
        return view('admin.referal.referal-codes');
    }

    /**
     * @return mixed
     */
    public function ajaxReferalCodes()
    {
        $referers = User::where('referal_code', '!=', null)->active();
        $datatables = app('datatables')->of($referers);
        return $datatables->make(true);
    }

    public function referalUsage()
    {
        return view('admin.referal.referal-usage');
    }

    /**
     * @return mixed
     */
    public function ajaxReferalUsage()
    {
        $referalUsage = ReferalUsage::with('referee', 'referer');
        $datatables = app('datatables')->of($referalUsage);
        $datatables = $datatables->addColumn('first_name', function($data) {
            return $data->referee ? $data->referee->first_name : 'N/A';
        })->addColumn('last_name', function($data) {
            return $data->referee ? $data->referee->last_name : 'N/A';
        })->addColumn('referee_email', function($data) {
            return $data->referee ? $data->referee->email : 'N/A';
        })->addColumn('referer_email', function($data) {
            return $data->referer ? $data->referer->email : 'N/A';
        })->addColumn('company', function($data) {
            return $data->referer && $data->referer->companies->first() ? $data->referer->companies->first()->name : 'N/A';
        })->editColumn('referal_code', function($data) {
            return route('referal.link', ["code" => $data->referal_code]);
//            return '<a herf=' .route('referal.link', ["code" => $data->referal_code]). '">'.$data->referal_code.'</a>';
        })->rawColumns(['referal_code']);
        return $datatables->make(true);
    }
}
