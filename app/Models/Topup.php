<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $table = "topup";
    protected $primaryKey = "id";
    public $timestamps = true;

    public function user(){
        return $this->hasOne(User::class, "username", "fk_username");
    }
}
