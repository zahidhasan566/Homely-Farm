<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = "Location";
    public $primaryKey = 'LocationCode';
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
