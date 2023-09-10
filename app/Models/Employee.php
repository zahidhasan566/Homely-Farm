<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = "Employee";
    public $primaryKey = 'EmployeeCode';
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing=false;
    protected $keyType = 'string';
}
