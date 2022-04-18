<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    use HasFactory;

    protected $table = "komentar";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $incrementing = true;

    function commenter(){
        return $this->hasOne(User::class, "username", "fk_user");
    }
}