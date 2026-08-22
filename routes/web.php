<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\BannerController;

Route::get('/', function () {
    return redirect()->route('home-page');
});

Route::get('/login', [LoginController::class, 'loginForm'])->name('login.form')->middleware('prevent.back.history');
Route::post('/login', [LoginController::class, 'unifiedLogin'])->name('login');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');
Route::post('/logout', [LoginController::class, 'logoutFrontend'])->name('logout');

Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

Route::get('/home-page', [HomepageController::class, 'index'])->name('home-page');

// Route Product

Route::get('/produk/detail/{id}', [ProductController::class, 'detail'])->name('produk.detail');
Route::get('/produk/kategori/{id}', [ProductController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/cari', [ProductController::class, 'search'])->name('produk.search');

Route::get('/artikel/all', [ArticleController::class, 'indexFrontend'])->name('artikel.all');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('article.show');




Route::middleware('is.customer')->group(function () {
    //route untuk Menampilkan halaman Akun Customer
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])->name('customer.akun');
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun'])->name('customer.updateakun');
    // Route untuk menambahkan produk ke keranjang 
    Route::post('add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart');
    Route::get('cart', [OrderController::class, 'viewCart'])->name('order.cart');
    Route::post('cart/update/{id}', [OrderController::class, 'updateCart'])->name('order.updateCart');
    Route::post('remove/{id}', [OrderController::class, 'removeFromCart'])->name('order.remove');
    Route::post('/order/select-pickup', [OrderController::class, 'selectPickup'])->name('order.selectPickup');
    Route::post('update-ongkir', [OrderController::class, 'updateOngkir'])->name('order.update-ongkir');
    Route::match(['get', 'post'], 'select-payment', [OrderController::class, 'selectPayment'])->name('order.selectpayment');
    Route::match(['get', 'post'], 'select-shipping', [OrderController::class, 'selectShipping'])->name('order.selectShipping');

    Route::post('/midtrans-callback', [OrderController::class, 'callback']);
    Route::get('/order/complete', [OrderController::class, 'complete'])->name('order.complete');

    Route::get('history', [OrderController::class, 'orderHistory'])->name('order.history');
    Route::get('order/invoice/{id}', [OrderController::class, 'invoiceFrontend'])->name('order.invoice');
    Route::get('/order/cod', [OrderController::class, 'cod'])->name('order.cod');
    Route::get('/order/bank-transfer', [OrderController::class, 'bankTransfer'])->name('order.bank_transfer');
    Route::get('/produk/all', [ProductController::class, 'index'])->name('produk.all');
});

Route::prefix('/backend')->middleware('auth:admin')->group(function () {
    // Dashboard backend
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend.dashboard');

    // Customer
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/{customer}', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/customer-data', [CustomerController::class, 'data'])->name('backend.customer.data');

    // Product
    Route::get('/product', [ProductController::class, 'indexbackend'])->name('product.index');
    Route::get('/product/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/product-data', [ProductController::class, 'data'])->name('backend.product.data');
    Route::post('/product/multiple-update-status', [ProductController::class, 'multipleUpdateStatus'])->name('backend.product.multipleUpdateStatus');


    // category
    Route::resource('category', CategoryController::class);
    Route::get('/category-data', [CategoryController::class, 'data'])->name('backend.category.data');

    // Order backend
    Route::get('pesanan-proses', [OrderController::class, 'statusProses'])->name('pesanan.proses');
    Route::get('pesanan-selesai', [OrderController::class, 'statusSelesai'])->name('pesanan.selesai');

    // Article
    Route::get('/article', [ArticleController::class, 'index'])->name('article.index');
    Route::get('/article/create', [ArticleController::class, 'create'])->name('article.create');
    Route::post('/article', [ArticleController::class, 'store'])->name('article.store');
    Route::get('/article/{article}/edit', [ArticleController::class, 'edit'])->name('article.edit');
    Route::put('/article/{article}', [ArticleController::class, 'update'])->name('article.update');
    Route::delete('/article/{article}', [ArticleController::class, 'destroy'])->name('article.destroy');
    Route::get('/article-data', [ArticleController::class, 'data'])->name('backend.article.data');


    // pesanan proses
    Route::get('/pesanan-proses', [OrderController::class, 'processOrder'])->name('pesanan.proses');
    Route::get('/pesanan-proses/{id}/edit', [OrderController::class, 'statusDetail'])->name('pesanan.proses.detail');
    Route::put('/pesanan-proses/{id}', [OrderController::class, 'statusUpdate'])->name('pesanan.proses.update');
    Route::get('/pesanan-proses/data', [OrderController::class, 'getProcessOrder'])->name('pesanan.proses.data');
    Route::get('/invoice/pesanan/{id}', [OrderController::class, 'invoiceBackend'])->name('invoice.backend');
    Route::delete('/pesanan/batalkan/{id}', [OrderController::class, 'batalkan'])
        ->middleware(['auth:admin']) // jika ada sistem role
        ->name('pesanan.batalkan');


    // pesanan selesai
    Route::get('/pesanan-selesai', [OrderController::class, 'finishedOrders'])->name('pesanan.selesai');
    Route::get('/pesanan-selesai/data', [OrderController::class, 'getFinishedOrders'])->name('pesanan.selesai.data');
    Route::get('/pesanan-selesai/{id}', [OrderController::class, 'showFinished'])->name('pesanan.selesai.show');

    // Laporan
    Route::get('/report/process', [ReportController::class, 'reportProcess'])->name('report.process');
    Route::get('/report/finished', [ReportController::class, 'reportFinished'])->name('report.finished');
    Route::match(['get', 'post'], 'backend/laporan/cetak-pesanan-proses', [ReportController::class, 'cetakOrderProses'])->name('backend.laporan.cetakpesananproses')->middleware('auth');
    Route::match(['get', 'post'], 'backend/laporan/cetak-pesanan-selesai', [ReportController::class, 'cetakOrderSelesai'])->name('backend.laporan.cetakpesananselesai')->middleware('auth');

    // Setting profil perusahaan
    Route::get('/company-setting', [CompanySettingController::class, 'index'])->name('company-setting.index');
    Route::post('/company-setting', [CompanySettingController::class, 'store'])->name('company-setting.store');
    Route::put('/company-setting/{id}', [CompanySettingController::class, 'update'])->name('company-setting.update');

    // Banner 
    Route::resource('banner', BannerController::class);
});
