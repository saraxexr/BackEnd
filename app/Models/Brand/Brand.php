<?php

namespace App\Models\Brand;

use App\Models\Model\MModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $table = "brands";
    protected $primarykey = "brandId";
    protected $fillable = ["brandName","brandNameInArabic", "field"];
    protected $hidden = ["laravel_through_key"];

    
}
