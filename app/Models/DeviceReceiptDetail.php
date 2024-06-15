<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReceiptDetail extends Model
{
    protected $table = 'device_receipt_details';

    protected $primaryKey = 'receipt_detail_id';

    protected $fillable = [
        'device_id',
        'receipt_id',
        'price',
    ];

    public $timestamps = false;

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function receipt()
    {
        return $this->belongsTo(DeviceReceipt::class, 'receipt_id');
    }
}
