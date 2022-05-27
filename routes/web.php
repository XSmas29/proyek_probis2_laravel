<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\UserController;
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

// Route::get('/', function () {
//     return view('welcome');
// });

// utk register
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);



// bagian customer
Route::prefix("/customer")->group(function(){
    Route::post('/getallproduct', [CustomerController::class, 'getAllProduct']);
    Route::post('/showproductbykategori', [CustomerController::class, 'showproductbykategori']);
    Route::post('/getdetailproduct', [CustomerController::class, 'getdetailproduct']);
    Route::post('/loadcarousel', [CustomerController::class, 'loadcarousel']);
    Route::post('/updatewishlist', [CustomerController::class, 'updatewishlist']);
    Route::post('/search', [CustomerController::class, 'search']);
    Route::post('/updatestock', [CustomerController::class, 'updatestock']);
    Route::post('/checkstockproduct', [CustomerController::class, 'checkStockProduct']);
    Route::post('/getoneproductandseller', [CustomerController::class, 'getOneProductAndSeller']);
    Route::post('/checkout', [CustomerController::class, 'checkout']);
    Route::post('/gethtrans', [CustomerController::class, 'getHTrans']);
    Route::post('/gettransdata', [CustomerController::class, 'getTransData']);
    Route::post('/completeorder', [CustomerController::class, 'completeorder']);
    Route::post('/sendreview', [CustomerController::class, 'sendreview']);
    Route::post('/getwishlist', [CustomerController::class, 'getwishlist']);
    Route::post('/getjumlahsaldo', [CustomerController::class, 'getjumlahsaldo']);
    Route::post('/tambahsaldo', [CustomerController::class, 'tambahsaldo']);
    Route::post('/getuserdata', [CustomerController::class, 'getuserdata']);
    Route::post('/updateprofile', [CustomerController::class, 'updateprofile']);
    Route::post('/getdtrans', [CustomerController::class, 'getDTransaksi']);
    Route::post('/getmutasisaldo', [CustomerController::class, 'getMutasiSaldo']);
});

//bagian seller
Route::prefix("/seller")->group(function(){
    Route::post('/listproduk', [SellerController::class, 'listProduk']);
    Route::post('/getkategori', [SellerController::class, 'getkategori']);
    Route::post('/addproduk', [SellerController::class, 'addProduk']);
    Route::post('/editproduk', [SellerController::class, 'editProduk']);
    Route::post('/getsaldo', [SellerController::class, 'getSaldo']);
    Route::post('/cairkansaldo', [SellerController::class, 'cairkanSaldo']);
    Route::post('/gettransaksi', [SellerController::class, 'getTransaksi']);
    Route::post('/updatetransaksi', [SellerController::class, 'updateTransaksi']);
    Route::post('/getreview', [SellerController::class, 'getReview']);
    Route::post('/updateprofile', [SellerController::class, 'updateprofile']);
    Route::post('/getdata', [SellerController::class, 'getdata']);
    Route::post('/getdatapendapatan', [SellerController::class, 'getdatapendapatan']);
    Route::post('/getkategoripalinglaku', [SellerController::class, 'getKategoriPalingLaku']);
});

Route::prefix("/admin")->group(function(){
    Route::post('/getusersdata', [AdminController::class, 'getusersdata']);
    Route::post('/updateprofile', [AdminController::class, 'updateprofile']);
    Route::post('/gettopupdata', [AdminController::class, 'gettopupdata']);
    Route::post('/getonetopupdata', [AdminController::class, 'getonetopupdata']);
    Route::post('/statustopupchange', [AdminController::class, 'statustopupchange']);
    Route::post('/getwithdrawdata', [AdminController::class, 'getwithdrawdata']);
    Route::post('/statuswithdrawchange', [AdminController::class, 'statuswithdrawchange']);
    Route::post('/getdata', [AdminController::class, 'getdata']);
});


