<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // utk login dan register

    public function login(Request $request){
        /*
            update changes =
            23 Maret : perbaikan jika ter-banned maka tidak bisa login, baik itu manual login maupun auto-login (remember me)
        */

        $username = $request->username;
        $password = $request->password;
        $ismanual = $request->ismanual; // 1 = manual, 0 = auto

        //check username
        $queryusername = DB::select("select * from user where username = '$username'");
        if (count($queryusername) == 0) {
            $response["code"]  = -3;
            $response["message"]  = "Username tidak ditemukan!";
        }else{
            $queryIsBan = DB::select("select * from user where username = '$username' and status = 1"); // check is it banned or not
            if (count($queryIsBan) == 0) {
                $response["code"]  = -4;
                $response["message"]  = "Anda telah dibanned!";
            }else{
                $isLoginSuccess = 1; // 1 = success, 0 = failed
                if ($ismanual == 1){
                    if (!password_verify($password, $queryusername[0]->password)) {
                        $isLoginSuccess = 0;
                    }
                }

                if ($isLoginSuccess == 1){
                    //login success
                    $registeras = $queryusername[0]->role;
                    $response["code"]  = 1; // customer
                    if ($registeras == "SELLER") {
                        $response["code"]  = 2; // seller
                    }

                    $response["message"]  = "Berhasil Login!";
                    foreach($queryusername as $row) {
                        $response["datauser"] = json_encode($row);          // only 1 object
                    }
                }else{
                    // password wrong
                    $response["code"]  = -2;
                    $response["message"]  = "Password salah!";
                }

            }
        }

        echo json_encode($response);
    }


    public function register(Request $request){
        $username = $request->username;
        $email = $request->email;
        $namalengkap = $request->namalengkap;
        $rekening = $request->rekening;
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        $registeras = $request->registeras;
        if ($registeras == "seller"){
            $namatoko = $request->namatoko;
        }

        // check username
        $query = DB::select("select * from user where username = '$username'");
        if (count($query) > 0) {
            $response["code"]  = -3;
            $response["message"]  = "Username telah digunakan!";
        }else{
            // check email
            $queryemail = DB::select("select * from user where email = '$email'");
            if (count($queryemail) > 0) {
                $response["code"]  = -2;
                $response["message"]  = "Email telah digunakan!";
            }else{
                // ins utk customer
                if ($registeras == "customer") {
                    $queryins = DB::insert("insert into user(username, password, email, nama, rekening, saldo, role, is_verified)
                            values('$username', '$password', '$email', '$namalengkap', '$rekening', 0, 'CUSTOMER', 1)");
                }else{
                    $queryins = DB::insert("insert into user(username, password, email, nama, rekening, saldo, toko, role, is_verified)
                            values('$username', '$password', '$email', '$namalengkap', '$rekening', 0, '$namatoko', 'SELLER', 1)");
                }
                $response["code"]  = 1;
                $response["message"]  = "Berhasil Register!";
            }
        }

        echo json_encode($response);
    }
}
