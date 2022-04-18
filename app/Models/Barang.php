<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = "barang";
    protected $primaryKey = "id";
    public $timestamps = false;

    function kategori(){
        return $this->hasOne(Kategori::class, "id", "fk_kategori");
    }

    function owner(){
        return $this->hasOne(User::class, "username", "fk_seller");
    }
}
