<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function index($action, $option = '')
    {
        if (!empty($option)) {
            Artisan::call("$action:$option");
        } else {
            Artisan::call("$action");
        }
        dd(Artisan::output());
    }
}
