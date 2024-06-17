<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceExportDetail extends Model
{
    protected $table = 'device_export_details';
    protected $primaryKey = 'export_detail_id';
    public $timestamps = false;

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function export()
    {
        return $this->belongsTo(DeviceExport::class, 'export_id', 'export_id');
    }
}
