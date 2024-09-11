<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = "Payment";
    public $primaryKey = 'MoneyRecNo';
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
