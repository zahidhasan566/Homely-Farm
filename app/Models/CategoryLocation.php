<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLocation extends Model
{
    use HasFactory;
    protected $table = "CategoryLocation";
    public $primaryKey = false;
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
