<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $primaryKey = 'device_id';

    protected $fillable = [
        'serial_number',
        'device_name',
        'device_type_id',
        'warranty_expiry',
        'state',
    ];

    public $timestamps = false;

    public function receiptDetails()
    {
        return $this->hasMany(DeviceReceiptDetail::class, 'device_id', 'device_id');
    }

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function deviceExportDetails()
    {
        return $this->hasMany(DeviceExportDetail::class, 'device_id');
    }
}
