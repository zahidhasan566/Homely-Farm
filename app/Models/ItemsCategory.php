<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemsCategory extends Model
{
    use HasFactory;
    protected $table = "ItemsCategory";
    public $primaryKey = 'CategoryCode';
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
