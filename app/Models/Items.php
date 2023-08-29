<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;
    protected $table = "Items";
    public $primaryKey = 'ItemCode';
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
