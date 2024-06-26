<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class District extends Model
{
    protected $table = 'district';

    protected $primaryKey = 'district_id';

    protected $fillable = [
        'district_name',
    ];

    public $timestamps = false;

    public function postOffices()
    {
        return $this->hasMany(PostOffice::class, 'district_id');
    }

    public function exportedDevices()
    {
        return $this->hasManyThrough(
            DeviceExport::class,
            PostOffice::class,
            'district_id', // Foreign key on PostOffice table...
            'post_office_id', // Foreign key on DeviceExport table...
            'district_id', // Local key on District table...
            'post_office_id' // Local key on PostOffice table...
        );
        // ->leftJoin('device_export_details', 'devices.device_id', '=', 'device_export_details.device_id')
        // ->leftJoin('device_exports', 'device_exports.export_id', '=', 'device_export_details.export_id')
        // ->leftJoin('post_office', 'post_office.post_office_id', '=', 'device_exports.post_office_id')
        // ->where('post_office.district_id', $this->district_id)
        // ->select('devices.*');
    }


}
