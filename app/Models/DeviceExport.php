<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceExport extends Model
{
    protected $table = 'device_exports';

    protected $primaryKey = 'export_id';

    protected $fillable = [
        'post_office_id',
        'user_id',
        'export_date',
    ];

    public $timestamps = false;

    public function postOffice()
    {
        return $this->belongsTo(PostOffice::class, 'post_office_id');
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id');
    }

    public function deviceExportDetails()
    {
        return $this->hasMany(DeviceExportDetail::class, 'export_id');
    }
}
