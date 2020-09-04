<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Api\TestModel;

class TestController extends Controller
{
    public function userInfo()
    {
        ini_set('memory_limit', 0);
        print_r(debug_backtrace());
//        debug_print_backtrace();

        dd(123);
        $testModle = new TestModel();
        $testModle->userInfo(22);
    }
}
