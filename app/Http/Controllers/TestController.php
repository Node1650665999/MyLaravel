<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Api\TestModel;

class TestController extends Controller
{
    public function userInfo()
    {
        $testModle = new TestModel();
        $testModle->userInfo(22);
    }
}
