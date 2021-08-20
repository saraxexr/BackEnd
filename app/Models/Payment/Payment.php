<?php

namespace App\Models\Payment;

use App\Models\Client\Client;
use App\Models\RRequest\Request;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = "payments";
    protected $primarykey = "paymentId";
    protected $fillable = ["requestId", "status", "isRefund"];

    public function requests(){
        return $this->hasOne(
            Request::class,
            "requestId",
            "requestId"
        );
    }

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
}
