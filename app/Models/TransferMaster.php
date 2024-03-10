<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferMaster extends Model
{
    use HasFactory;
    protected $table = "TransferMaster";
    public $primaryKey = "TransferCode";
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing=false;
    protected $keyType = 'string';

    public function ReceiveMaster()
    {
        return $this->belongsTo(ReceiveMaster::class,'TransferCode','TransferCode');
    }
    public function TransferDetails()
    {
        return $this->hasMany(TransferDetails::class,'TransferCode','TransferCode');
    }
    public function Category()
    {
        return $this->belongsTo(ItemsCategory::class,'CategoryCode','CategoryCode');
    }
}
