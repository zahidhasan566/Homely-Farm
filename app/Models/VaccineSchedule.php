<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineSchedule extends Model
{
    use HasFactory;
    protected $table = "VaccineSchedule";
    public $primaryKey = 'ScheduleCode';
    protected $guarded = [];
    public $timestamps = false;
    protected $keyType = 'string';
}
