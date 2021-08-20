<?php

namespace App\Models\Supplier;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = "suppliers";
    protected $primarykey = "supplierId";
    protected $fillable = ["supplierId","companyInArabic","companyInEnglish", "verified", "companyCertificate"];
    

    public function account(){
        return $this->hasOne(
            User::class,
            "uid",
            "supplierId"
        );
    }



}
