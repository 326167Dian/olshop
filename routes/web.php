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
use App\Http\Controllers\PromoController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryAdminController;
use App\Http\Controllers\InventorySetheaderController;
use App\Http\Controllers\InventoryCarabayarController;
use App\Http\Controllers\InventoryPelangganController;
use App\Http\Controllers\InventoryCekdarahController;
use App\Http\Controllers\InventoryKonselingController;
use App\Http\Controllers\InventoryMesoController;
use App\Http\Controllers\InventoryPioController;
use App\Http\Controllers\InventoryPtoController;
use App\Http\Controllers\InventoryCppController;
use App\Http\Controllers\InventoryHomecareController;
use App\Http\Controllers\InventorySwamedikasiController;
use App\Http\Controllers\InventorySupplierController;
use App\Http\Controllers\InventorySatuanController;
use App\Http\Controllers\InventoryJenisobatController;

Route::get('/', function () {
    return redirect()->route('home-page');
});

// Login pelanggan/member (Google saja)
Route::get('/login', [LoginController::class, 'loginForm'])->name('login.form')->middleware('prevent.back.history');
Route::post('/logout', [LoginController::class, 'logoutFrontend'])->name('logout');

// Login admin/staf (username & password)
Route::get('/staf', [LoginController::class, 'adminLoginForm'])->name('admin.login.form')->middleware('prevent.back.history');
Route::post('/staf', [LoginController::class, 'adminLogin'])->name('admin.login');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

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
    Route::post('/order/bank-transfer', [OrderController::class, 'bankTransfer'])->name('order.bank_transfer');
    Route::get('/produk/all', [ProductController::class, 'index'])->name('produk.all');
});

Route::prefix('/backend')->middleware('auth:admin')->group(function () {
    // Dashboard backend
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend.dashboard');
    Route::get('/dashboard/check-new-order', [DashboardController::class, 'checkNewOrder'])->name('backend.dashboard.checkNewOrder');

    // Customer
    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/{customer}', [CustomerController::class, 'show'])->name('customer.show');
    Route::get('/customer-data', [CustomerController::class, 'data'])->name('backend.customer.data');
    Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    // Product
    Route::get('/product', [ProductController::class, 'indexbackend'])->name('product.index');
    Route::get('/product/select-category', [ProductController::class, 'selectCategory'])->name('product.selectCategory');
    Route::get('/product-select-category-data', [ProductController::class, 'selectCategoryData'])->name('backend.product.selectCategoryData');
    Route::put('/product/{product}/update-category', [ProductController::class, 'updateCategory'])->name('product.updateCategory');
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
    Route::get('/report/visits', [ReportController::class, 'reportVisits'])->name('report.visits');
    Route::match(['get', 'post'], 'backend/laporan/cetak-pesanan-proses', [ReportController::class, 'cetakOrderProses'])->name('backend.laporan.cetakpesananproses')->middleware('auth');
    Route::match(['get', 'post'], 'backend/laporan/cetak-pesanan-selesai', [ReportController::class, 'cetakOrderSelesai'])->name('backend.laporan.cetakpesananselesai')->middleware('auth');

    // Setting profil perusahaan
    Route::get('/company-setting', [CompanySettingController::class, 'index'])->name('company-setting.index');
    Route::post('/company-setting', [CompanySettingController::class, 'store'])->name('company-setting.store');
    Route::put('/company-setting/{id}', [CompanySettingController::class, 'update'])->name('company-setting.update');

    // Banner
    Route::resource('banner', BannerController::class);

    // Promo
    Route::resource('promo', PromoController::class);

    // Profil admin (foto profil)
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
});

// Sistem Inventory (adaptasi Laravel dari public/apotekberlian, database yang sama)
Route::prefix('inventory')->middleware(['auth:admin', 'admin.active'])->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/sales-chart-data', [InventoryController::class, 'salesChartData'])->name('sales-chart-data');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [InventoryAdminController::class, 'index'])->name('index');
        Route::get('/create', [InventoryAdminController::class, 'create'])->name('create');
        Route::post('/', [InventoryAdminController::class, 'store'])->name('store');
        Route::get('/login-logs', [InventoryAdminController::class, 'loginLogs'])->name('login-logs');
        Route::get('/{admin}/edit', [InventoryAdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [InventoryAdminController::class, 'update'])->name('update');
        Route::delete('/{admin}', [InventoryAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('setheader')->middleware('inventory.module:mheader')->name('setheader.')->group(function () {
        Route::get('/', [InventorySetheaderController::class, 'index'])->name('index');
        Route::put('/', [InventorySetheaderController::class, 'update'])->name('update');
    });

    Route::prefix('carabayar')->middleware('inventory.module:mjenisbayar')->name('carabayar.')->group(function () {
        Route::get('/', [InventoryCarabayarController::class, 'index'])->name('index');
        Route::get('/create', [InventoryCarabayarController::class, 'create'])->name('create');
        Route::post('/', [InventoryCarabayarController::class, 'store'])->name('store');
        Route::get('/{carabayar}/edit', [InventoryCarabayarController::class, 'edit'])->name('edit');
        Route::put('/{carabayar}', [InventoryCarabayarController::class, 'update'])->name('update');
        Route::delete('/{carabayar}', [InventoryCarabayarController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pelanggan')->middleware('inventory.module:mpelanggan')->name('pelanggan.')->group(function () {
        Route::get('/', [InventoryPelangganController::class, 'index'])->name('index');
        Route::get('/create', [InventoryPelangganController::class, 'create'])->name('create');
        Route::post('/', [InventoryPelangganController::class, 'store'])->name('store');
        Route::get('/{pelanggan}/edit', [InventoryPelangganController::class, 'edit'])->name('edit');
        Route::put('/{pelanggan}', [InventoryPelangganController::class, 'update'])->name('update');
        Route::delete('/{pelanggan}', [InventoryPelangganController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('supplier')->middleware('inventory.module:msupplier')->name('supplier.')->group(function () {
        Route::get('/', [InventorySupplierController::class, 'index'])->name('index');
        Route::get('/create', [InventorySupplierController::class, 'create'])->name('create');
        Route::post('/', [InventorySupplierController::class, 'store'])->name('store');
        Route::get('/print', [InventorySupplierController::class, 'print'])->name('print');
        Route::post('/obat-search', [InventorySupplierController::class, 'obatSearch'])->name('obat-search');
        Route::delete('/barang/{barangSupplier}', [InventorySupplierController::class, 'hapusBarang'])->name('hapus-barang');
        Route::get('/{supplier}/edit', [InventorySupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [InventorySupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [InventorySupplierController::class, 'destroy'])->name('destroy');
        Route::get('/{supplier}/dataobat', [InventorySupplierController::class, 'dataobat'])->name('dataobat');
        Route::post('/{supplier}/dataobat', [InventorySupplierController::class, 'simpanBarang'])->name('simpan-barang');
        Route::get('/{supplier}/hutang', [InventorySupplierController::class, 'hutang'])->name('hutang');
    });

    Route::prefix('satuan')->middleware('inventory.module:msatuan')->name('satuan.')->group(function () {
        Route::get('/', [InventorySatuanController::class, 'index'])->name('index');
        Route::get('/create', [InventorySatuanController::class, 'create'])->name('create');
        Route::post('/', [InventorySatuanController::class, 'store'])->name('store');
        Route::get('/{satuan}/edit', [InventorySatuanController::class, 'edit'])->name('edit');
        Route::put('/{satuan}', [InventorySatuanController::class, 'update'])->name('update');
        Route::delete('/{satuan}', [InventorySatuanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('jenisobat')->middleware('inventory.module:mjenisobat')->name('jenisobat.')->group(function () {
        Route::get('/', [InventoryJenisobatController::class, 'index'])->name('index');
        Route::get('/create', [InventoryJenisobatController::class, 'create'])->name('create');
        Route::post('/', [InventoryJenisobatController::class, 'store'])->name('store');
        Route::get('/{jenisobat}/edit', [InventoryJenisobatController::class, 'edit'])->name('edit');
        Route::put('/{jenisobat}', [InventoryJenisobatController::class, 'update'])->name('update');
        Route::delete('/{jenisobat}', [InventoryJenisobatController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('cekdarah')->middleware('inventory.module:cekdarah')->name('cekdarah.')->group(function () {
        Route::get('/', [InventoryCekdarahController::class, 'index'])->name('index');
        Route::get('/create', [InventoryCekdarahController::class, 'create'])->name('create');
        Route::post('/', [InventoryCekdarahController::class, 'store'])->name('store');
        Route::get('/{cekdarah}/edit', [InventoryCekdarahController::class, 'edit'])->name('edit');
        Route::put('/{cekdarah}', [InventoryCekdarahController::class, 'update'])->name('update');
        Route::delete('/{cekdarah}', [InventoryCekdarahController::class, 'destroy'])->name('destroy');
        Route::get('/{cekdarah}/print', [InventoryCekdarahController::class, 'print'])->name('print');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('konseling')->name('konseling.')->group(function () {
        Route::get('/', [InventoryKonselingController::class, 'index'])->name('index');
        Route::get('/create', [InventoryKonselingController::class, 'create'])->name('create');
        Route::post('/', [InventoryKonselingController::class, 'store'])->name('store');
        Route::get('/{konseling}/edit', [InventoryKonselingController::class, 'edit'])->name('edit');
        Route::put('/{konseling}', [InventoryKonselingController::class, 'update'])->name('update');
        Route::delete('/{konseling}', [InventoryKonselingController::class, 'destroy'])->name('destroy');
        Route::get('/{konseling}/print', [InventoryKonselingController::class, 'print'])->name('print');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('meso')->name('meso.')->group(function () {
        Route::get('/', [InventoryMesoController::class, 'index'])->name('index');
        Route::get('/create', [InventoryMesoController::class, 'create'])->name('create');
        Route::post('/', [InventoryMesoController::class, 'store'])->name('store');
        Route::get('/{meso}', [InventoryMesoController::class, 'show'])->name('show');
        Route::get('/{meso}/edit', [InventoryMesoController::class, 'edit'])->name('edit');
        Route::put('/{meso}', [InventoryMesoController::class, 'update'])->name('update');
        Route::delete('/{meso}', [InventoryMesoController::class, 'destroy'])->name('destroy');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('pio')->name('pio.')->group(function () {
        Route::get('/', [InventoryPioController::class, 'index'])->name('index');
        Route::get('/create', [InventoryPioController::class, 'create'])->name('create');
        Route::post('/', [InventoryPioController::class, 'store'])->name('store');
        Route::get('/{pio}', [InventoryPioController::class, 'show'])->name('show');
        Route::get('/{pio}/edit', [InventoryPioController::class, 'edit'])->name('edit');
        Route::put('/{pio}', [InventoryPioController::class, 'update'])->name('update');
        Route::delete('/{pio}', [InventoryPioController::class, 'destroy'])->name('destroy');
    });

    // Tidak digerbang flag admin manapun di legacy (edit/hapus khusus pemilik, dicek di controller).
    Route::prefix('pto')->name('pto.')->group(function () {
        Route::get('/', [InventoryPtoController::class, 'index'])->name('index');
        Route::get('/riwayat', [InventoryPtoController::class, 'riwayat'])->name('riwayat');
        Route::get('/export-pdf', [InventoryPtoController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/create', [InventoryPtoController::class, 'create'])->name('create');
        Route::post('/', [InventoryPtoController::class, 'store'])->name('store');
        Route::get('/{pto}', [InventoryPtoController::class, 'show'])->name('show');
        Route::get('/{pto}/edit', [InventoryPtoController::class, 'edit'])->name('edit');
        Route::put('/{pto}', [InventoryPtoController::class, 'update'])->name('update');
        Route::delete('/{pto}', [InventoryPtoController::class, 'destroy'])->name('destroy');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('cpp')->name('cpp.')->group(function () {
        Route::get('/', [InventoryCppController::class, 'index'])->name('index');
        Route::get('/create', [InventoryCppController::class, 'create'])->name('create');
        Route::post('/', [InventoryCppController::class, 'store'])->name('store');
        Route::get('/{cpp}', [InventoryCppController::class, 'show'])->name('show');
        Route::get('/{cpp}/edit', [InventoryCppController::class, 'edit'])->name('edit');
        Route::put('/{cpp}', [InventoryCppController::class, 'update'])->name('update');
        Route::delete('/{cpp}', [InventoryCppController::class, 'destroy'])->name('destroy');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('homecare')->name('homecare.')->group(function () {
        Route::get('/', [InventoryHomecareController::class, 'index'])->name('index');
        Route::get('/create', [InventoryHomecareController::class, 'create'])->name('create');
        Route::post('/', [InventoryHomecareController::class, 'store'])->name('store');
        Route::get('/{homecare}', [InventoryHomecareController::class, 'show'])->name('show');
        Route::get('/{homecare}/edit', [InventoryHomecareController::class, 'edit'])->name('edit');
        Route::put('/{homecare}', [InventoryHomecareController::class, 'update'])->name('update');
        Route::delete('/{homecare}', [InventoryHomecareController::class, 'destroy'])->name('destroy');
    });

    // Tidak digerbang flag admin manapun di legacy (semua admin yang login bisa akses).
    Route::prefix('swamedikasi')->name('swamedikasi.')->group(function () {
        Route::get('/', [InventorySwamedikasiController::class, 'index'])->name('index');
        Route::get('/riwayat', [InventorySwamedikasiController::class, 'riwayat'])->name('riwayat');
        Route::get('/export-pdf', [InventorySwamedikasiController::class, 'exportPdf'])->name('export-pdf');
        Route::post('/obat-search', [InventorySwamedikasiController::class, 'obatSearch'])->name('obat-search');
        Route::post('/', [InventorySwamedikasiController::class, 'store'])->name('store');
        Route::get('/{riwayat}/edit', [InventorySwamedikasiController::class, 'edit'])->name('edit');
        Route::put('/{riwayat}', [InventorySwamedikasiController::class, 'update'])->name('update');
        Route::delete('/{riwayat}', [InventorySwamedikasiController::class, 'destroy'])->name('destroy');
        Route::post('/{riwayat}/followup', [InventorySwamedikasiController::class, 'followup'])->name('followup');
    });
});
