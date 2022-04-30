<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'register', 'login', 'customer/showproductbykategori', 'customer/getdetailproduct'
        , '/customer/loadcarousel', 'customer/updatewishlist', 'customer/search'
        , 'customer/updatestock', 'customer/getallproduct', 'customer/getoneproductandseller'
        , 'customer/checkstockproduct', 'customer/checkout', 'customer/gethtrans'
        , 'customer/gettransdata', 'customer/completeorder', 'customer/sendreview'
        , 'customer/getwishlist', 'customer/getjumlahsaldo', 'customer/tambahsaldo'
        , 'customer/getuserdata', 'customer/updateprofile', 'customer/getdtrans'
        , 'seller/listproduk', 'seller/getkategori', 'seller/addproduk', 'seller/editproduk'
        , 'seller/getsaldo', 'seller/cairkansaldo', 'seller/gettransaksi', 'seller/updatetransaksi'
        , 'seller/getreview', 'seller/updateprofile', 'seller/getdata', 'seller/getdatapendapatan'
        , 'admin/getusersdata', 'admin/updateprofile', 'admin/gettopupdata', 'admin/getonetopupdata'
        , 'admin/statustopupchange', 'admin/getwithdrawdata', 'admin/getonewithdrawdata'
        , 'admin/statuswithdrawchange', 'admin/getdata', 'customer/getmutasisaldo'
    ];
}
