<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDetails extends Model
{
    use HasFactory;
    protected $table = "TransferDetails";
    public $primaryKey = "TransferCode";
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing=false;
    protected $keyType = 'string';

    public function TransferItems()
    {
        return $this->belongsTo(Items::class,'ItemCode','ItemCode');
    }

}
