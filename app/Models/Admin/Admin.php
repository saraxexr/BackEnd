<?php

namespace App\Models\Admin;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $table= "admins";
    protected $primarykey = "adminId";
    protected $fillable = ["adminId", "enterId"];

    public function account(){
        return $this->hasOne(
            User::class,
            "uid",
            "adminId"
        );
    }

}
