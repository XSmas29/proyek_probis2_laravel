<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = "review";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $incrementing = true;

    function reviewer(){
        return $this->hasOne(User::class, "username", "fk_user");
    }

    // function headertrans(){
    //     return $this->hasOne(HTrans::class, "id", "fk_htrans");
    // }

    // function detailtrans(){
    //     return $this->hasOne(DTrans::class, "id", "fk_dtrans");
    // }
}
