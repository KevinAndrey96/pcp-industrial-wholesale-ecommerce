<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/users/{id}/passwordEdit', [App\Http\Controllers\UsersController::class, 'passwordEdit']);
    Route::post('/users/passwordUpda', [App\Http\Controllers\UsersController::class, 'passwordUpda']);
    Route::get('/ironAsoc', [App\Http\Controllers\UsersController::class, 'ironAsoc']);

    Route::get('/distributors', [App\Http\Controllers\ProductsController::class, 'productsDistriIron']);
    Route::get('/ironmongers', [App\Http\Controllers\ProductsController::class, 'productsDistriIron']);

    Route::get('/orders', [App\Http\Controllers\OrdersController::class, 'index']);
    Route::post('/orderProducts', [App\Http\Controllers\OrderProductsController::class, 'trolley']);
    Route::post('/bill', [App\Http\Controllers\OrderProductsController::class, 'bill'])->name('bill');
    Route::get('/showOrders', [App\Http\Controllers\OrderProductsController::class, 'showOrder']);
    Route::get('/detailOrder', [App\Http\Controllers\OrderProductsController::class, 'detailOrder']);
    Route::get('/detailMyOrder', [App\Http\Controllers\OrderProductsController::class, 'detailMyOrder']);
    Route::post('/changeStatus', [App\Http\Controllers\OrderProductsController::class, 'changeStatus']);
    Route::post('/downloadPDF', [App\Http\Controllers\OrderProductsController::class, 'downloadPDF']);
    Route::get('/myOrders', [App\Http\Controllers\OrderProductsController::class, 'myOrders']);
    Route::post('/quotation', [App\Http\Controllers\OrderProductsController::class, 'quotation']);
    Route::get('/orderReturn', [App\Http\Controllers\OrderProductsController::class, 'orderReturn']);

    Route::middleware('admin')->group(function () {
        Route::get('/users', [App\Http\Controllers\UsersController::class, 'index']);
        Route::get('/users/create', [App\Http\Controllers\UsersController::class, 'create']);
        Route::post('/users', [App\Http\Controllers\UsersController::class, 'store']);
        Route::get('/users/{user}/edit', [App\Http\Controllers\UsersController::class, 'edit']);
        Route::match(['put', 'patch'], '/users/{user}', [App\Http\Controllers\UsersController::class, 'update']);
        Route::delete('users/{id}', [App\Http\Controllers\UsersController::class, 'destroy']);
        Route::post('/chooseListUser', [App\Http\Controllers\UsersController::class, 'chooseListUser']);
        Route::post('importUsers', [App\Http\Controllers\UsersController::class, 'importUsers']);

        Route::get('/list/createList', [App\Http\Controllers\PriceListController::class, 'create']);
        Route::post('/lists', [App\Http\Controllers\PriceListController::class, 'store']);
        Route::get('/lists', [App\Http\Controllers\PriceListController::class, 'index']);
        Route::get('/lists/{id}/edit', [App\Http\Controllers\PriceListController::class, 'edit']);
        Route::match(['put', 'patch'], '/lists/{list}', [App\Http\Controllers\PriceListController::class, 'update']);
        Route::delete('lists/{id}', [App\Http\Controllers\PriceListController::class, 'destroy']);

        Route::get('/products/{id}', [App\Http\Controllers\ProductsController::class, 'index']);
        Route::get('/products/create/{id}', [App\Http\Controllers\ProductsController::class, 'create']);
        Route::post('/products', [App\Http\Controllers\ProductsController::class, 'store']);
        Route::get('/products/{id}/edit', [App\Http\Controllers\ProductsController::class, 'edit']);
        Route::match(['put', 'patch'], '/products/{product}', [App\Http\Controllers\ProductsController::class, 'update']);
        Route::delete('products/{product}', [App\Http\Controllers\ProductsController::class, 'destroy']);
        Route::get('exportProducts/{id}', [App\Http\Controllers\ProductsController::class, 'export']);
        Route::get('/chooseList', [App\Http\Controllers\ProductsController::class, 'chooseList']);
        Route::post('importProducts/{id}', [App\Http\Controllers\ProductsController::class, 'import']);

        Route::get('/allTransfers', [App\Http\Controllers\OrderProductsController::class, 'allTransfers']);
    });
});
