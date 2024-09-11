<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDetails extends Model
{
    use HasFactory;
    protected $table = "PaymentDetails";
    protected $guarded = [];
    public $timestamps = false;
}
