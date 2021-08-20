<?php

namespace App\Models\Moderator;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moderator extends Model
{
    use HasFactory;
    
    protected $table = "moderators";
    protected $primarykey = "moderatorId";
    protected $fillable = ["moderatorId", "enterId"];

    public $timestamps = false;
    
    public function account(){
        return $this->hasOne(
            User::class,
            "uid",
            "moderatorId"
        );
    }

}
