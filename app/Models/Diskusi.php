<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskusi extends Model
{
    use HasFactory;

    protected $table = "diskusi";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $incrementing = true;

    function author(){
        return $this->hasOne(User::class, "username", "fk_user");
    }

    function comments(){
        return $this->hasMany(Komentar::class, "fk_diskusi", "id");
    }
}
