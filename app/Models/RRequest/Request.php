<?php

namespace App\Models\RRequest;

use App\Models\Brand\Brand;
use App\Models\Client\Client;
use App\Models\Model\MModel;
use App\Models\Supplier\Supplier;
use App\Models\User\User;
use ClientsModelsBridge;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 

class Request extends Model
{
    use HasFactory;
    protected $table = "requests";
    protected $primarykey = "requestId";
    protected $fillable = ["requestNum","description", "address", "model" ,"requestStatus" ,"field", "quantity" ,"amounts", "finalAmount", "clientId", "brandId" ,"supplierId", "shipperName"];
    protected $hidden = ["clientId","supplierId"];

    public function clients(){
        return $this->hasOneThrough(
            User::class,
            Request::class,
            "requestId",
            "uid",
            "requestId",
            "clientId"
        );
    }

    public function brands(){
        return $this->hasOneThrough(
            Brand::class,
            Request::class,
            "requestId",
            "brandId",
            "requestId",
            "brandId"
        );
    }

    public function suppliers(){
        return $this->hasOneThrough(
            User::class,
            Request::class,
            "requestId",
            "uid",
            "requestId",
            "supplierId"
        );
    }


}
