<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseMaster extends Model
{
    use HasFactory;
    protected $table = "ExpenseMaster";
    public $primaryKey = 'ExpenseCode';
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing=false;
    protected $keyType = 'string';
}
