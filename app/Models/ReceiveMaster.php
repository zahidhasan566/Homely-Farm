<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveMaster extends Model
{
    use HasFactory;
    protected $table = "ReceiveMaster";
    public $primaryKey = "ReceiveCode";
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing=false;
    protected $keyType = 'string';

    public function ReceiveDetails()
    {
        return $this->hasMany(ReceiveDetails::class,'ReceiveCode','ReceiveCode');
    }
    public function Category()
    {
        return $this->belongsTo(ItemsCategory::class,'CategoryCode','CategoryCode');
    }
}
