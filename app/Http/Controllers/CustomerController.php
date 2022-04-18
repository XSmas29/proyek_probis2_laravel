<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Diskusi;
use App\Models\DTrans;
use App\Models\HTrans;
use App\Models\Kategori;
use App\Models\Review;
use App\Models\Topup;
use App\Models\Mutasi;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Environment\Console;

class CustomerController extends Controller
{
    //
    public function showproductbykategori(Request $request){ //  dipanggil di fragment customer home
        // $query = "select * from barang, kategori, user
        //         where barang.fk_kategori = kategori.id and user.username = barang.fk_seller";

        // if ($request->function == "showproductbykategori") {
            $querykategori = Kategori::all();

            $response["datakategori"] = $querykategori;

            // $response["dataproduct"] = [];

            // foreach ($querykategori as $row) {
            //     $query = Barang::where("fk_kategori", "=", $row->id)->inRandomOrder()->limit(4)->get();
            //     //$arr[] = $query;

            //     //$arr["dataproduct".$ctr] = $query;
            //     $response["dataproduct"][] = $query;
            //     $ctr++;

            // }

            $queryproduct = Barang::all();
            $response["dataproduct"] = $queryproduct;

            $querywishlist = Wishlist::all();
            $response["datawishlist"] = $querywishlist;


            echo json_encode($response);
        //}

    }

    public function loadcarousel(){ //  dipanggil di fragment customer home
        $foto = Barang::inRandomOrder()->limit(8)->get();

        $response["foto"] = $foto;
        echo json_encode($response);
    }

    public function getdetailproduct(Request $request){
        $idproduct = $request->idproduct;

        $query = Barang::find($idproduct);

        $response["datadetailproduct"] = $query;

        $queryseller = User::where("username", "=", $query->fk_seller)->get();
        $response["dataseller"] = $queryseller;

        $queryreview = Review::select("review.id", "review.fk_htrans",
                                        "review.fk_dtrans", "review.fk_user",
                                         "review.star", "review.isi", "user.nama", "htrans.tanggal")
            ->join("user", "user.username", "=", "review.fk_user")
            ->join("htrans", "htrans.id", "=", "review.fk_htrans")
            ->join("dtrans", "dtrans.id", "=", "review.fk_dtrans")
            ->where("dtrans.fk_barang", "=", $idproduct)
            ->orderBy("htrans.tanggal", "desc")->get();
        $response["datareview"] = $queryreview;

        // mengambil product-product yg kategorinya sama utk rekomendasi
        // foreach ($query as $q) {
            $queryproducts = Barang::where("fk_kategori", "=", $query->fk_kategori)
            ->where("id", "!=", $idproduct)
            ->inRandomOrder()->limit(5)->get();
            $response["datarecommendproduct"] = $queryproducts;
        // }

        echo json_encode($response);
    }

    public function updatewishlist(Request $request){
        $function = $request->function;
        if ($function == "addwishlist") {
            $query = new Wishlist();
            $query->fk_user = $request->username;
            $query->fk_barang = $request->idproduct;
            $query->save();


            $response["code"] = 1;
            $response["message"] = "Berhasil Menambah Wishlist";
        }
        else  {
            $idwishlist = $request->idwishlist;
            $query = Wishlist::find($idwishlist);
            $query->delete();

            $response["code"] = 1;
            $response["message"] = "Berhasil Menghapus Wishlist";
        }

        $querywishlist = Wishlist::all();
        $response["datawishlist"] = $querywishlist;

        echo json_encode($response);
    }

    public function search(Request $request){
        $keyword = $request->keyword;
        $databarang = Barang::where('nama','LIKE','%'.$keyword.'%');

        //filter
        if ($request->kategori != "All"){
            $kategori = Kategori::where('nama','=', $request->kategori)->get()[0];
            $databarang = $databarang->where("fk_kategori", "=", $kategori->id);
        }
        if ($request->min != "" && $request->min >= 0){
            $databarang = $databarang->where("harga", ">=", (int)$request->min);
        }
        if ($request->max != "" && $request->max >= 0){
            $databarang = $databarang->where("harga", "<=", (int)$request->max);
        }
        $databarang = $databarang->get();

        $datakategori = Kategori::all();
        $dataseller = [];
        foreach($databarang as $barang){
            $dataseller[] = $barang->owner;
        }

        $response["databarang"] = $databarang;
        $response["datakategori"] = $datakategori;
        echo json_encode($response);
    }

    public function updatestock(Request $request){
        $idproduct = $request->idproduct;
        $jumlah = (int)$request->jumlah;

        $querygetstock = Barang::find($idproduct);
        $jumlah = $querygetstock->stok - $jumlah;

        if ($jumlah >= 0) {
            $querygetstock->stok = $jumlah;
            $querygetstock->save();

            $querybarang = Barang::all();

            $response["code"] = 1;
            $response["message"] = "sukses";
            $response["dataproduct"] = $querybarang;
        }else{
            $response["code"] = 0;
            $response["message"] = "gagal";
        }
        echo json_encode($response);
    }

    public function getAllProduct(Request $request){
        $querybarang = Barang::all();
        $response["dataproduct"] = $querybarang;
        echo json_encode($response);
    }

    public function checkStockProduct(Request $request){
        $querybarang = Barang::find($request->idproduct);
        $stock = $querybarang->stok;
        if ($stock > 0) {
            $response["code"] = 1;
            $response["message"] = "Stok Barang Tersedia!";
        }else{
            $response["code"] = 0;
            $response["message"] = "Maaf Stok Barang Sudah Habis!";
        }

        echo json_encode($response);
    }

    public function getOneProductAndSeller(Request $request){
        $queryproduct = Barang::find($request->idproduct);
        $response["dataproduct"] = $queryproduct;

        $queryseller = User::find($queryproduct->fk_seller);
        $response["dataseller"] = $queryseller;

        echo json_encode($response);
    }


    public function checkout(Request $request){
        /*
            update changes :
            - 14 April 2022 : merubah dari 1 transaksi banyak barang banyak toko mennjadi 1 transaksi hanya untuk satu toko. Jadi 1 transaksi bisa banyak barang dari 1 toko yang sama tapi tidak bisa 1 transaksi banyak toko.

        */

        $cart = json_decode($request->cart);
        $username = $request->username;
        $grandtotal = $request->grandtotal; // ini grandtotal dari semua transaksi dalam sekali checkout

        $user = User::find($username);

        if ($user->saldo < $grandtotal) {
            $response["code"] = 0;
            $response["message"] = "Saldo tidak cukup!";
        }else{
            // pertama, mendapatkan username toko dari cart yang dikirim dari aplikasi
            $fk_seller = [];
            foreach ($cart ?? [] as $produk) {
                if (count($fk_seller) ==  0){
                    $fk_seller[] = $produk->fk_seller;
                }else{
                    $is_counted = 0; // 1 = tercatat di $fk_seller, 0 1 = belum tercatat di $fk_seller,
                    foreach ($fk_seller as $seller) {
                        if ($seller == $produk->fk_seller) {
                            $is_counted = 1;
                        }
                    }

                    if ($is_counted == 0) {
                        $fk_seller[] = $produk->fk_seller;
                    }
                }
            }


            // kedua, waktunya mencatat/menuliskan di htrans dan dtrans
            $ctr_success = 0;
            foreach ($fk_seller as $seller) {
                DB::beginTransaction();
                $header = new HTrans();
                $header->fk_customer = $username;

                $gpt = 0; // ini variable penyimpan grandtotal per transaksi, bukan grandtotal semua transaksi dalam sekali checkout
                foreach($cart ?? [] as $produk){ // menghitung grandtotal per transaksi
                    if ($produk->fk_seller == $seller) {
                        $gpt += $produk->jumlah * $produk->harga;
                    }
                }
                $header->grandtotal = $gpt;
                $header->tanggal = date("Y/m/d");
                $result = $header->save();

                //add ke tabel mutasi
                $mutasi = new Mutasi();
                $mutasi->fk_user = $username;
                $mutasi->jumlah = $gpt * -1;
                $mutasi->tanggal = date("Y-m-d h:i:s");
                $mutasi->keterangan = "transaksi #".$header->id;
                $mutasi->save();

                foreach($cart ?? [] as $produk){
                    if ($produk->fk_seller == $seller) {
                        $barang = Barang::find($produk->idProduct);
                        $detail = new DTrans();
                        $detail->fk_htrans = $header->id;
                        $detail->fk_barang = $barang->id;
                        $detail->jumlah = $produk->jumlah;
                        $detail->subtotal = $produk->jumlah * $produk->harga;
                        $detail->fk_seller = $barang->owner->username;
                        $detail->notes_customer = $produk->catatan;
                        $detail->status = "pending";
                        $result = $result && $detail->save();

                        // mengurangi stok barang
                        $barang->stok = $barang->stok - $produk->jumlah;
                        $result = $result && $barang->save();

                        if ($result == false)
                        {
                            DB::rollBack();
                            //abort(400, "Purchase Failed!");

                        }
                    }

                }
                if ($result == true)
                {
                    DB::commit();
                    $ctr_success += 1;
                }
            }

            if ($ctr_success == count($fk_seller)) {
                $user->saldo -= $grandtotal;
                $user->save();
                if ($result == true)
                {
                    $response["code"] = 1;
                    $response["message"] = "Berhasil melakukan pembelian. Terima kasih!";
                }
            }else{
                $response["code"] = -1;
                $response["message"] = "Terjadi error ketika melakukan checkout";
                //$response["message"] = $ctr_success;
            }

        }

        echo json_encode($response);
    }

    public function getHTrans(Request $request){ // berfungsi menampilkan list dari header trans
        /*
            update change =
            - 16 Maret 2022 : sudah tidak digunakan

            - 15 April : digunakan kembali
        */
        $username = $request->username;

        $queryhtrans = HTrans::where("fk_customer", "=", $username)
                    ->orderBy("id", "desc")->get();
        $response["datahtrans"] = $queryhtrans;


        echo json_encode($response);
    }

    public function getTransData(Request $request){ // berfungsi untuk melihat detail dari dtrans
        /*
            update change =
            - 16Maret2022 : berubah dari menampilkan banyak detail transaksi menjadi hanya menampilkan SATU detail transaksi / satu barang detail

            - 15 April : kembali ke codingan sebelum 16 Maret 2022
        */
        $idhtrans = $request->idhtrans;

        $queryhtrans = HTrans::find($idhtrans);
        $response["datahtrans"] = $queryhtrans;

        $querydtrans = DTrans::where("fk_htrans", "=", $idhtrans)->get();
        $response["datadtrans"] = $querydtrans;

        $queryreview = Review::where("fk_htrans", "=", $idhtrans)->get();
        $response["datareview"] = $queryreview;

        foreach ($querydtrans as $q) {
            $queryseller = User::find($q->fk_seller);
            $dataseller[] = $queryseller;

            $querybarang = Barang::find($q->fk_barang);
            $databarang[] = $querybarang;
        }
        $response["dataseller"] = $dataseller;
        $response["dataproduct"] = $databarang;

        echo json_encode($response);
    }

    public function completeorder(Request $request){
        $iddtrans = $request->iddtrans;
        $querydtrans = DTrans::find($iddtrans);
        $querydtrans->status = "completed";
        $result = $querydtrans->save();

        if ($result) {
            $seller = User::findOrFail($querydtrans->fk_seller);
            $seller->saldo += $querydtrans->subtotal;
            $seller->save();
            $response["code"] = 1;
            $response["message"] = "Berhasil complete order!";
        }else{
            $response["code"] = 0;
            $response["message"] = "Gagal complete order!";
        }

        echo json_encode($response);
    }

    public function sendreview(Request $request){
        $iddtrans = $request->iddtrans;
        $fk_htrans = $request->fk_htrans;
        $fk_user = $request->fk_user;
        $star = $request->star;
        $isi = $request->isi;

        $query = new Review();
        $query->fk_htrans = $fk_htrans;
        $query->fk_dtrans = $iddtrans;
        $query->fk_user = $fk_user;
        $query->star = $star;
        if ($isi == "" || $isi== null) {
            $query->isi = "";
        }else{
            $query->isi = $isi;
        }

        $result = $query->save();

        if ($result) {
            $response["code"] = 1;
            $response["message"] = "Terima kasih atas review-nya!";
        }else{
            $response["code"] = 0;
            $response["message"] = "Gagal memberikan review!";
        }

        echo json_encode($response);
    }

    public function getwishlist(Request $request){
        $username = $request->username;

        $querywishlist = Wishlist::where("fk_user", "=", $username)
                            ->orderBy("id", "desc")
                            ->get();
        $response["datawishlist"] = $querywishlist;

        echo json_encode($response);
    }

    public function getjumlahsaldo(Request $request){
        $username = $request->username;

        $queryuser = User::find($username);

        $response["datauser"] = $queryuser;

        $querytopup = Topup::where("fk_username", "=", $username)
                        ->orderBy("id", "desc")->get();
        $response["datatopup"] = $querytopup;

        if (!$queryuser) {
            $response["code"] = 0;
            $response["message"] = "Gagal mendapatkan saldo!";
        }else{
            $response["code"] = 1;
            $response["message"] = "Berhasil mendapatkan saldo!";
        }

        echo json_encode($response);
    }

    public function tambahsaldo(Request $request){
        $username = $request->username;
        $ammount = (int)$request->ammount;
        $foto = $request->foto;
        $ext = $request->ext;

        $querysaldo = new Topup();
        $querysaldo->fk_username = $username;
        $querysaldo->jumlah_topup = $ammount;
        $querysaldo->status_topup = 0;
        $result = $querysaldo->save();

        $namafile = "bukti_".$querysaldo->id.".".$ext;
        file_put_contents("bukti/".$namafile, base64_decode($foto));
        $querysaldo->bukti_topup = $namafile;

        // $ext = $request->file('bukti')->getClientOriginalExtension();
        // $picture = $request->file('bukti');
        // $picture->move('bukti', 'bukti_'.$topup->id.'.'.$ext);
        // $topup->bukti_topup = 'bukti_'.$topup->id.'.'.$ext;

        $result = $querysaldo->save();

        if ($result) {
            $response["code"] = 1;
            $response["message"] = "Berhasil request top up saldo!";
        }else{
            $response["code"] = 0;
            $response["message"] = "Gagal request top up saldo!";
        }

        // $response["code"] = 1;
        // $response["message"] = "Berhasil request top up saldo!";

        echo json_encode($response);
    }

    public function getuserdata(Request $request){
        $username = $request->username;
        $queryuser = User::find($username);
        $response["datauser"] = $queryuser;
        echo json_encode($response);
    }

    public function updateprofile(Request $request){
        $isCont = 1;

        $oldusername = $request->oldusername;
        $username = $request->username;
        $email = $request->email;
        $nama = $request->namalengkap;
        $rekening = $request->rekening;
        $password = $request->password;
        $foto = $request->foto;
        $ext = $request->ext;

        $queryuser = User::find($oldusername);

        $queryuser->username = $username;
        $queryuser->email = $email;
        $queryuser->nama = $nama;
        $queryuser->rekening = $rekening;
        // if ($isCont == 1) {
        //     $result = $queryuser->save();
        //     if (!$result) {
        //         $isCont = 0;
        //         $response["code"] = 0;
        //         $response["message"] = "Gagal update profile!";
        //     }
        // }



        if ($password != null && $isCont == 1) {
            if (password_verify($request->oldpassword, $queryuser->password)) {
                if ($request->oldpassword == $password) {
                    $isCont = 0;
                    $response["code"] = 0;
                    $response["message"] = "Password baru tidak boleh sama dengan password lama!";
                }else{
                    $password = password_hash($request->password, PASSWORD_DEFAULT);
                    $queryuser->password = $password;
                    // if ($isCont == 1) {
                    //     $result = $queryuser->save();
                    //     if (!$result) {
                    //         $isCont = 0;
                    //         $response["code"] = 0;
                    //         $response["message"] = "Gagal update profile!";
                    //     }
                    // }
                }

            }else{
                $isCont = 0;
                $response["code"] = 0;
                $response["message"] = "Password lama tidak sesuai!";
            }
        }

        if ($foto != null && $ext != null && $isCont == 1) {
            $namafile = "picture_".$queryuser->username.".".$ext;
            file_put_contents("profile/".$namafile, base64_decode($foto));
            $queryuser->gambar = $namafile;

            // if ($isCont == 1) {
            //     $result = $queryuser->save();
            //     if (!$result) {
            //         $isCont = 0;
            //         $response["code"] = 0;
            //         $response["message"] = "Gagal update profile!";
            //     }
            // }
        }

        if ($isCont == 1) {
            $result = $queryuser->save();
            if (!$result) {
                $isCont = 0;
                $response["code"] = 0;
                $response["message"] = "Gagal update profile!";
            }else{
                // ganti username di tempat lain
                if ($username != $oldusername) {
                    // ubah di htrans
                    $queryhtrans = HTrans::where("fk_customer", "=", $oldusername)->get();
                    foreach ($queryhtrans as $q) {
                        // $q->fk_customer = $username;
                        // $q->save();
                        $qh = HTrans::find($q->id);
                        $qh->fk_customer = $username;
                        $qh->save();
                    }

                    // ubah di Review
                    $queryreview = Review::where("fk_user", "=", $oldusername)->get();
                    foreach ($queryreview as $q) {
                        // $q->fk_user = $username;
                        // $q->save();
                        $qr = Review::find($q->id);
                        $qr->fk_user = $username;
                        $qr->save();
                    }

                    // ubah di TopUp
                    $querytopup = Topup::where("fk_username", "=", $oldusername)->get();
                    foreach ($querytopup as $q) {
                        // $q->fk_user = $username;
                        // $q->save();
                        $qt = Topup::find($q->id);
                        $qt->fk_username = $username;
                        $qt->save();
                    }

                    // ubah di Wishlist
                    $querywishlist = Wishlist::where("fk_user", "=", $oldusername)->get();
                    foreach ($querywishlist as $q) {
                        // $q->fk_user = $username;
                        // $q->save();
                        $qw = Wishlist::find($q->id);
                        $qw->fk_user = $username;
                        $qw->save();
                    }

                }
                $response["code"] = 1;
                $response["message"] = "Berhasil update profile!";
                $response["datauser"] = $queryuser;
            }
        }

        // if ($result && $isCont == 1) {
        //     $response["code"] = 1;
        //     $response["message"] = "Berhasil update profile!";
        //     $response["datauser"] = $queryuser;
        // }else{
        //     $response["code"] = 0;
        //     $response["message"] = "Gagal update profile!";
        // }

        echo json_encode($response);
    }


    public function getDTransaksi(Request $request){
        /*
            Created date = 16 Maret 2022
        */
        $username = $request->username;
        //$transaksi = DTrans::where("username", "=", $username)->get();
        $querytrans = DTrans::select("dtrans.id", "dtrans.fk_htrans",
                                        "dtrans.fk_barang", "dtrans.jumlah",
                                         "dtrans.subtotal", "dtrans.rating",
                                         "dtrans.review", "dtrans.fk_seller",
                                         "dtrans.status", "dtrans.notes_seller",
                                         "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("htrans.fk_customer", "=", $username)
                                    ->orderBy("htrans.tanggal", "desc")->get();
        $databarang = [];
        if ($request->status != "All"){
            $querytrans = DTrans::select("dtrans.id", "dtrans.fk_htrans",
                                            "dtrans.fk_barang", "dtrans.jumlah",
                                            "dtrans.subtotal", "dtrans.rating",
                                            "dtrans.review", "dtrans.fk_seller",
                                            "dtrans.status", "dtrans.notes_seller",
                                                                "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("htrans.fk_customer", "=", $username)
                                    ->where("status", "=", strtolower($request->status))
                                    ->orderBy("htrans.tanggal", "desc")->get();
        }

        foreach ($querytrans as $trans){
            $databarang[] = $trans->product;
        }

        $response["datadtrans"] = $querytrans;

        echo json_encode($response);
    }
}
