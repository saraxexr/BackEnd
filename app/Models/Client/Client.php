<?php

namespace App\Models\Client;

use App\Models\Brand\Brand;
use App\Models\RRequest\Request;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table="clients";
    protected $primarykey= "clientId";
    protected $fillable = ["clientId","address"];
    protected $hidden= ["laravel_through_key"];

    public function requests(){
        return $this->hasMany(
            Request::class,
            "clientId",
            "clientId"
        );
    }

    public function brands(){
        return $this->hasManyThrough(
            Brand::class,
            Request::class,
            "clientId",
            "brandId",
            "clientId",
            "brandId"
        );
    }

    public function account(){
        return $this->hasOne(
            User::class,
            "uid",
            "clientId"
        );
    }
}
