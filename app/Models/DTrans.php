<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DTrans extends Model
{
    use HasFactory;

    protected $table = "dtrans";
    protected $primaryKey = "id";
    public $timestamps = false;

    public function product(){
        return $this->hasOne(Barang::class, "id", "fk_barang");
    }

    public function header(){
        return $this->hasOne(HTrans::class, "id", "fk_htrans");
    }
}
