<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DTrans;
use App\Models\HTrans;
use App\Models\Kategori;
use App\Models\Review;
use App\Models\Topup;
use App\Models\Mutasi;
use App\Models\User;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    //
    public function listProduk(Request $request){
        $dataproduk = Barang::where("fk_seller", "=", $request->login);
        if ($request->kategori != "All"){
            $kategori = Kategori::where('nama','=', $request->kategori)->get()[0];
            $dataproduk = $dataproduk->where("fk_kategori", "=", $kategori->id);
        }
        $dataproduk = $dataproduk->get();

        $response["dataproduk"] = $dataproduk;


        echo json_encode($response);
    }

    public function getkategori(){
        $datakategori = Kategori::all();
        $response["datakategori"] = $datakategori;

        echo json_encode($response);
    }

    public function addProduk(Request $request){
        $kategori = Kategori::where('nama','=', $request->kategori)->get()[0];

        $produk = new Barang();
        $produk->nama = $request->nama;
        $produk->fk_seller = $request->seller;
        $produk->fk_kategori = $kategori->id;
        $produk->deskripsi = $request->desc;
        $produk->harga = $request->harga;
        $produk->stok = $request->stok;
        $produk->save();
        //$produk->gambar = $request->gambar;

        $imageStr = $request->input('gambar');
        $decodeImage = base64_decode($imageStr);
        //        $image = fwrite($decodeImage);

        if (!empty($imageStr)) {
            file_put_contents('produk/produk_'.$produk->id.'.jpg', base64_decode($imageStr));
        }

        $produk->gambar = 'produk_'.$produk->id.'.jpg';
        $produk->save();

        $response["code"] = 1;

        echo json_encode($response);
    }

    public function editProduk(Request $request){
        $kategori = Kategori::where('nama','=', $request->kategori)->get()[0];

        $produk = Barang::findOrFail($request->id);
        $produk->nama = $request->nama;
        $produk->fk_kategori = $kategori->id;
        $produk->deskripsi = $request->desc;
        $produk->harga = $request->harga;
        $produk->stok = $request->stok;
        //$produk->gambar = $request->gambar;
        $produk->save();


        $imageStr = $request->input('gambar');
        $decodeImage = base64_decode($imageStr);
        //        $image = fwrite($decodeImage);

        if (!empty($imageStr)) {
            file_put_contents('produk/produk_'.$produk->id.'.jpg', base64_decode($imageStr));
        }

        $produk->gambar = 'produk_'.$produk->id.'.jpg';
        $produk->save();

        $response["code"] = 2;

        echo json_encode($response);
    }

    public function getSaldo(Request $request){
        $response["saldo"] = User::find($request->login);
        $response["datatopup"] = Topup::where("fk_username", "=", $request->login)->get();

        echo json_encode($response);
    }

    public function cairkanSaldo(Request $request){
        $user = User::find($request->login);

        if($user->saldo >= $request->jumlah){
            $user->saldo = $user->saldo - $request->jumlah;
            $user->save();
            $topup = new Topup();
            $topup->fk_username = $request->login;
            $topup->jumlah_topup = $request->jumlah;
            $topup->status_topup = 0;
            $topup->save();

            //add ke tabel mutasi
            $mutasi = new Mutasi();
            $mutasi->fk_user = $topup->fk_username;
            $mutasi->jumlah = $topup->jumlah_topup * -1;
            $mutasi->tanggal = date("Y-m-d h:i:s");
            $mutasi->keterangan = "withdraw #".$topup->id;
            $mutasi->save();

            $response["code"] = 1;
        }
        else{
            $response["code"] = -1;
        }

        echo json_encode($response);
    }

    public function getTransaksi(Request $request){
        /*
            Update Changes :
            - 22 April 2022 : merubah dari menampilkan dtrans menjadi htrans
        */
        $username = $request->username;
        //$transaksi = DTrans::all();
        $transaksi = DTrans::distinct()->select("dtrans.id", "dtrans.fk_htrans",
                                        "dtrans.fk_barang", "dtrans.jumlah",
                                         "dtrans.subtotal", "dtrans.rating",
                                         "dtrans.review", "dtrans.fk_seller",
                                         "dtrans.status", "dtrans.notes_seller",
                                         "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->orderBy("htrans.tanggal", "desc")->get();

        $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                    "htrans.grandtotal", "htrans.tanggal")
                                ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                ->where("dtrans.fk_seller", "=", $username)
                                ->orderBy("htrans.tanggal", "desc")->get();

        $databarang = [];
        if ($request->status != "All"){
            //$transaksi = DTrans::where("status", "=", strtolower($request->status))->get();
            $transaksi = DTrans::select("dtrans.id", "dtrans.fk_htrans",
                                            "dtrans.fk_barang", "dtrans.jumlah",
                                            "dtrans.subtotal", "dtrans.rating",
                                            "dtrans.review", "dtrans.fk_seller",
                                            "dtrans.status", "dtrans.notes_seller",
                                                                "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->where("status", "=", strtolower($request->status))
                                    ->orderBy("htrans.tanggal", "desc")->get();


            $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                        "htrans.grandtotal", "htrans.tanggal")
                                    ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->where("dtrans.status", "=", strtolower($request->status))
                                    ->orderBy("htrans.tanggal", "desc")->get();

        }

        foreach ($transaksi as $trans){
            $databarang[] = $trans->product;
        }

        $response["datatrans"] = $transaksi;
        $response["datahtrans"] = $htrans;


        echo json_encode($response);
    }

    public function updateTransaksi(Request $request){
        /*

            Update Changes :
            - 22 April 2022 : berubah dari yg hanya mengubah satu dtrans, menjadi bisa banyak dtrans


        */

        $detail = json_decode($request->detail);
        //dd($detail);
        $addmutasi = false;
        $buyer = "";
        $dtrans = "";
        $subtotal = 0;

        foreach ($detail as $d) {
            $dtrans = DTrans::find($d->id);
            $dtrans->status = $request->status;

            if ($request->status == "rejected"){
                $buyer = User::findOrFail($dtrans->header->fk_customer);
                $buyer->saldo += $dtrans->subtotal;
                $dtrans->save();
                $buyer->save();
                $response["code"] = 1;
                $subtotal += $dtrans->subtotal;
                $addmutasi = true;
            }
            else if ($request->status == "processing"){
                $barang = Barang::find($dtrans->fk_barang);
                if ($barang->stok >= $dtrans->jumlah){
                    $barang->stok -= $dtrans->jumlah;
                    $dtrans->save();
                    $response["code"] = 2;
                }
                else{
                    $response["code"] = -1;
                }
            }
            else if ($request->status == "sent"){
                $dtrans->notes_seller = $d->notes_seller;
                $dtrans->save();
                $response["code"] = 3;
            }
        }

        if ($addmutasi == true){
            //add ke tabel mutasi
            $mutasi = new Mutasi();
            $mutasi->fk_user = $buyer->username;
            $mutasi->jumlah = $subtotal;
            $mutasi->tanggal = date("Y-m-d h:i:s");
            $mutasi->keterangan = "transaksi #".$dtrans->fk_htrans." rejected";
            $mutasi->save();
        }

        //$response["data"] = $detail;



        // $detail = DTrans::find($request->id);
        // $detail->status = $request->status;

        // if ($request->status == "rejected"){
        //     $buyer = User::findOrFail($detail->header->fk_customer);
        //     $buyer->saldo += $detail->subtotal;
        //     $detail->save();
        //     $buyer->save();
        //     $response["code"] = $buyer->saldo;
        // }
        // else if ($request->status == "processing"){
        //     $barang = Barang::find($detail->fk_barang);
        //     if ($barang->stok >= $detail->jumlah){
        //         $barang->stok -= $detail->jumlah;
        //         $detail->save();
        //         $response["code"] = 2;
        //     }
        //     else{
        //         $response["code"] = -1;
        //     }
        // }
        // else if ($request->status == "sent"){
        //     $detail->notes_seller = $request->noteseller;
        //     $detail->save();
        //     $response["code"] = 3;
        // }


        echo json_encode($response);
    }

    public function getReview(Request $request){
        $review = Review::where("fk_dtrans", "=", $request->id)->get()[0];
        $response["review"] = $review;
        echo json_encode($response);
    }

    public function updateprofile(Request $request){
        $isCont = 1;

        $oldusername = $request->oldusername;
        $username = $request->username;
        $email = $request->email;
        $nama = $request->namalengkap;
        $rekening = $request->rekening;
        $toko = $request->toko;
        $password = $request->password;
        $foto = $request->foto;
        $ext = $request->ext;

        $queryuser = User::find($oldusername);

        $queryuser->username = $username;
        $queryuser->email = $email;
        $queryuser->nama = $nama;
        $queryuser->rekening = $rekening;
        $queryuser->toko = $toko;
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

    public function getdata(Request $request){
        $datauser = User::find($request->login);

        $datadetail = DTrans::where("fk_seller", "=", $request->login)->get();

        $response["datauser"] = $datauser;
        $response["datadetail"] = $datadetail;

        echo json_encode($response);
    }

    public function getDataPendapatan(Request $request){
        /*

            created at : 28 April 2022 untuk menampilkan data pendapatan seller

        */

        $username = $request->username;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        //$transaksi = DTrans::all();
        $transaksi = DTrans::distinct()->select("dtrans.id", "dtrans.fk_htrans",
                                        "dtrans.fk_barang", "dtrans.jumlah",
                                         "dtrans.subtotal", "dtrans.rating",
                                         "dtrans.review", "dtrans.fk_seller",
                                         "dtrans.status", "dtrans.notes_seller",
                                         "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->orderBy("htrans.tanggal", "desc")->get();

        $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                    "htrans.grandtotal", "htrans.tanggal")
                                ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                ->where("dtrans.fk_seller", "=", $username)
                                ->orderBy("htrans.tanggal", "desc")->get();

        if ($bulan != 0 && $tahun != "-- Pilih Tahun --"){
            $tgl = $tahun . "-" . $bulan;
            $transaksi = DTrans::distinct()->select("dtrans.id", "dtrans.fk_htrans",
                                            "dtrans.fk_barang", "dtrans.jumlah",
                                            "dtrans.subtotal", "dtrans.rating",
                                            "dtrans.review", "dtrans.fk_seller",
                                            "dtrans.status", "dtrans.notes_seller",
                                            "dtrans.notes_customer")
                                        ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                        ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                        ->where("dtrans.fk_seller", "=", $username)
                                        ->whereYear("htrans.tanggal", "=", $tahun)
                                        ->whereMonth("htrans.tanggal", "=", $bulan)
                                        ->orderBy("htrans.tanggal", "desc")->get();

            $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                            "htrans.grandtotal", "htrans.tanggal")
                                        ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                        ->where("dtrans.fk_seller", "=", $username)
                                        ->whereYear("htrans.tanggal", "=", $tahun)
                                        ->whereMonth("htrans.tanggal", "=", $bulan)
                                        ->orderBy("htrans.tanggal", "desc")->get();
        }

        $databarang = [];
        if ($request->status != "All"){
            //$transaksi = DTrans::where("status", "=", strtolower($request->status))->get();
            $transaksi = DTrans::select("dtrans.id", "dtrans.fk_htrans",
                                            "dtrans.fk_barang", "dtrans.jumlah",
                                            "dtrans.subtotal", "dtrans.rating",
                                            "dtrans.review", "dtrans.fk_seller",
                                            "dtrans.status", "dtrans.notes_seller",
                                                                "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->where("status", "=", strtolower($request->status))
                                    ->orderBy("htrans.tanggal", "desc")->get();


            $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                        "htrans.grandtotal", "htrans.tanggal")
                                    ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->where("dtrans.status", "=", strtolower($request->status))
                                    ->orderBy("htrans.tanggal", "desc")->get();


            if ($bulan != 0 && $tahun != "-- Pilih Tahun --"){
                $tgl = $tahun . "-" . $bulan;
                $transaksi = DTrans::distinct()->select("dtrans.id", "dtrans.fk_htrans",
                                                "dtrans.fk_barang", "dtrans.jumlah",
                                                "dtrans.subtotal", "dtrans.rating",
                                                "dtrans.review", "dtrans.fk_seller",
                                                "dtrans.status", "dtrans.notes_seller",
                                                "dtrans.notes_customer")
                                            ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                            ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                            ->where("dtrans.fk_seller", "=", $username)
                                            ->whereYear("htrans.tanggal", "=", $tahun)
                                            ->whereMonth("htrans.tanggal", "=", $bulan)
                                            ->where("status", "=", strtolower($request->status))
                                            ->orderBy("htrans.tanggal", "desc")->get();

                $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                                "htrans.grandtotal", "htrans.tanggal")
                                            ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                            ->where("dtrans.fk_seller", "=", $username)
                                            ->whereYear("htrans.tanggal", "=", $tahun)
                                            ->whereMonth("htrans.tanggal", "=", $bulan)
                                            ->where("dtrans.status", "=", strtolower($request->status))
                                            ->orderBy("htrans.tanggal", "desc")->get();
            }

        }

        // foreach ($transaksi as $trans){
        //     $databarang[] = $trans->product;
        // }

        $response["datatrans"] = $transaksi;
        $response["datahtrans"] = $htrans;


        echo json_encode($response);
    }

    public function getKategoriPalingLaku(Request $request){
        $username = $request->username;

        // mendapatkan kategori
        $kategori = Kategori::all();
        $response["datakategori"] = $kategori;

        // mendapatkan data barang yg dimiliki seller
        $barang = Barang::where("fk_seller", "=", $username)->get();
        $response["dataproduct"] = $barang;

        //mendapatkan dtrans dari transaksi seller
        $dtrans = DTrans::where("fk_seller", "=", $username)->get();
        $response["datadtrans"] = $dtrans;

        if($kategori && $barang && $dtrans){
            $response["code"] = 1;
            $response["message"] = "berhasil mendapatkan data";
        }else{
            $response["code"] = -1;
            $response["message"] = "gagal mendapatkan data";
        }
        echo json_encode($response);
    }
}
