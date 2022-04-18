<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = "wishlist";
    protected $primaryKey = "id";
    public $timestamps = false;

    function barang(){
        return $this->hasOne(Barang::class, "id", "fk_barang");
    }
}
