<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function ListAllOrders()
    {
        return view('pages.orders.listallorders.index');
    }
    public function AddNewOrder()
    {
        return view('pages.orders.addneworder.index');
    }
}
