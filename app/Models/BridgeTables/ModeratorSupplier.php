<?php

namespace App\Models\BridgeTables;

use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeratorSupplier extends Model
{
    use HasFactory;
    protected $table= "moderators_suppliers";

}
