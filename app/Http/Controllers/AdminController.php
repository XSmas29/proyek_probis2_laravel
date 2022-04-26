<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Diskusi;
use App\Models\DTrans;
use App\Models\HTrans;
use App\Models\Review;
use App\Models\Topup;
use App\Models\User;
use App\Models\Mutasi;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function getusersdata(Request $request){
        $queryusers = User::where("username", "!=", "superadmin")->get();

        if ($queryusers) {
            $response["code"] = 1;
            $response["message"] = "Berhasil mengambil data user";
            $response["datausers"] = $queryusers;
        }else{
            $response["code"] = 0;
            $response["message"] = "Gagal mengambil data user";
        }


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
        $toko = $request->toko;
        $status = $request->status;

        $queryuser = User::find($oldusername);

        $queryuser->username = $username;
        $queryuser->email = $email;
        $queryuser->nama = $nama;
        $queryuser->rekening = $rekening;
        $queryuser->status = $status;
        // if ($isCont == 1) {
        //     $result = $queryuser->save();
        //     if (!$result) {
        //         $isCont = 0;
        //         $response["code"] = 0;
        //         $response["message"] = "Gagal update profile!";
        //     }
        // }



        if ($password != null && $isCont == 1) {
            // if (password_verify($request->oldpassword, $queryuser->password)) {
                // if ($request->oldpassword == $password) {
                //     $isCont = 0;
                //     $response["code"] = 0;
                //     $response["message"] = "Password baru tidak boleh sama dengan password lama!";
                // }else{
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
                // }

            // }else{
            //     $isCont = 0;
            //     $response["code"] = 0;
            //     $response["message"] = "Password lama tidak sesuai!";
            // }
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

        if ($toko != null) {
            $queryuser->toko = $toko;
        }


        if ($isCont == 1) {
            $result = $queryuser->save();
            if (!$result) {
                $isCont = 0;
                $response["code"] = 0;
                $response["message"] = "Gagal update profile!";
            }else{
                if ($username != $oldusername) {
                    // ubah di Barang
                    $querybarang = Barang::where("fk_seller", "=", $oldusername)->get();
                    foreach ($querybarang as $q) {
                        // $q->fk_user = $username;
                        // $q->save();
                        $qr = Barang::find($q->id);
                        $qr->fk_seller = $username;
                        $qr->save();
                    }

                    // ubah di htrans
                    $querydtrans = DTrans::where("fk_seller", "=", $oldusername)->get();
                    foreach ($querydtrans as $q) {
                        // $q->fk_customer = $username;
                        // $q->save();
                        $qd = DTrans::find($q->id);
                        $qd->fk_seller = $username;
                        $qd->save();
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

                }

                $response["code"] = 1;
                $response["message"] = "Berhasil update profile!";
                $response["datauser"] = $queryuser;
            }
        }

        echo json_encode($response);
    }

    public function gettopupdata(Request $request){
        $querytopup = Topup::where("status_topup", "=", 0)
                        ->where("bukti_topup", "!=", null)
                        ->orderBy("id", "desc")->get();
        $response["datatopup"] = $querytopup;

        $queryuser = User::where("status", "!=", 0)->get();
        $response["datauser"] = $queryuser;

        echo json_encode($response);
    }

    public function getwithdrawdata(Request $request){
        $querytopup = Topup::where("status_topup", "=", 0)
                        ->where("bukti_topup", "=", null)
                        ->orderBy("id", "desc")->get();
        $response["datatopup"] = $querytopup;

        $queryuser = User::where("status", "!=", 0)->get();
        $response["datauser"] = $queryuser;

        echo json_encode($response);
    }

    public function getonetopupdata(Request $request){
        $querytopup = Topup::find($request->idtopup);
        $response["datatopup"] = $querytopup;

        $queryuser = User::find($querytopup->fk_username);
        $response["datauser"] = $queryuser;

        echo json_encode($response);
    }

    public function statusTopUpChange(Request $request){
        $idtopup = $request->idtopup;
        $status_topup = (int) $request->status_topup;

        $querytopup = Topup::find($idtopup);
        $querytopup->status_topup = $status_topup;
        $result = $querytopup->save();

        if ($result) {
            if ($status_topup == 1) {
                $queryuser = User::find($querytopup->fk_username);
                $queryuser->saldo = $queryuser->saldo + $querytopup->jumlah_topup;
                $result = $result + $queryuser->save();

                //add ke tabel mutasi
                $mutasi = new Mutasi();
                $mutasi->fk_user = $queryuser->username;
                $mutasi->jumlah = $querytopup->jumlah_topup;
                $mutasi->tanggal = date("Y-m-d h:i:s");
                $mutasi->keterangan = "topup saldo";
                $mutasi->save();

                $response["message"] = "Berhasil konfirmasi top up!";
            }else{

                $response["message"] = "Berhasil reject top up!";
            }

            if ($result) {
                $response["code"] = 1;
            }else{
                $response["code"] = 0;
                $response["message"] = "Gagal konfirmasi top up!";
            }
        }

        echo json_encode($response);
    }

    public function statusWithdrawChange(Request $request){
        $idtopup = $request->idwithdraw;
        $status_topup = (int) $request->status_topup;

        $querytopup = Topup::find($idtopup);

        $queryuser = User::find($querytopup->fk_username);

        if ($queryuser->saldo - $querytopup->jumlah_topup > 0) {
            $querytopup->status_topup = $status_topup;
            $result = $querytopup->save();

            if ($result) {
                if ($status_topup == 1) {
                    // $queryuser = User::find($querytopup->fk_username);
                    // $queryuser->saldo = $queryuser->saldo - $querytopup->jumlah_topup;
                    // $result = $result + $queryuser->save();


                    $response["message"] = "Berhasil konfirmasi withdraw!";
                }else{
                    $queryuser = User::find($querytopup->fk_username);
                    $queryuser->saldo = $queryuser->saldo + $querytopup->jumlah_topup;
                    $result = $result + $queryuser->save();

                    $response["message"] = "Berhasil reject withdraw!";
                }

                if ($result) {
                    $response["code"] = 1;
                }else{
                    $response["code"] = 0;
                    $response["message"] = "Gagal konfirmasi withdraw!";
                }
            }
        }else{
            $response["code"] = 0;
            $response["message"] = "Gagal konfirmasi withdraw!";
        }



        echo json_encode($response);
    }

    public function getdata(){
        $datauser = User::all();
        $datatopup = Topup::all();
        $response["datauser"] = $datauser;
        $response["datatopup"] = $datatopup;

        echo json_encode($response);
    }
}
