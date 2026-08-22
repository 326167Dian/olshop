<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Article;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $customers = User::all();
        $articles = Article::all();
        $orders = Order::whereNotIn('status', ['Selesai', 'pending', 'Dibatalkan'])->get();
        return view('backend.dashboard.index', compact('products', 'customers', 'articles', 'orders'));
    }
}
