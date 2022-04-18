<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DTrans;
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

            $response["code"] = 1;
        }
        else{
            $response["code"] = -1;
        }

        echo json_encode($response);
    }

    public function getTransaksi(Request $request){
        $username = $request->username;
        //$transaksi = DTrans::all();
        $transaksi = DTrans::select("dtrans.id", "dtrans.fk_htrans",
                                        "dtrans.fk_barang", "dtrans.jumlah",
                                         "dtrans.subtotal", "dtrans.rating",
                                         "dtrans.review", "dtrans.fk_seller",
                                         "dtrans.status", "dtrans.notes_seller",
                                         "dtrans.notes_customer")
                                    ->join("htrans", "htrans.id", "=", "dtrans.fk_htrans")
                                    ->join("barang", "barang.id", "=", "dtrans.fk_barang")
                                    ->where("dtrans.fk_seller", "=", $username)
                                    ->orderBy("htrans.tanggal", "asc")->get();

        $htrans = HTrans::distinct()->select("htrans.id", "htrans.fk_customer",
                                    "htrans.grandtotal", "htrans.tanggal")
                                ->join("dtrans", "htrans.id", "=", "dtrans.fk_htrans")
                                ->where("dtrans.fk_seller", "=", $username)
                                ->orderBy("htrans.tanggal", "asc")->get();

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
                                    ->orderBy("htrans.tanggal", "asc")->get();


        }

        foreach ($transaksi as $trans){
            $databarang[] = $trans->product;
        }

        $response["datahtrans"] = $htrans;
        $response["datatrans"] = $transaksi;



        echo json_encode($response);
    }

    public function updateTransaksi(Request $request){
        $detail = DTrans::find($request->id);
        $detail->status = $request->status;

        if ($request->status == "rejected"){
            $buyer = User::findOrFail($detail->header->fk_customer);
            $buyer->saldo += $detail->subtotal;
            $detail->save();
            $buyer->save();

            $response["code"] = $buyer->saldo;
        }
        else if ($request->status == "processing"){
            $barang = Barang::find($detail->fk_barang);
            if ($barang->stok >= $detail->jumlah){
                $barang->stok -= $detail->jumlah;
                $detail->save();
                $response["code"] = 2;
            }
            else{
                $response["code"] = -1;
            }
        }
        else if ($request->status == "sent"){
            $detail->notes_seller = $request->noteseller;
            $detail->save();
            $response["code"] = 3;
        }

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
}
