<?php

namespace App\Http\Controllers;

use App\Tool\Validate\Aes;
use Illuminate\Http\Request;

use App\Http\Requests;
use Ramsey\Uuid\Uuid;

class TestController extends Controller
{
    public function index()
    {
        return Aes::letSecret(2, 'E', config('app.receipt_token'));
        return Uuid::uuid1();
    }
}
