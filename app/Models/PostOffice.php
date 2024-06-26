<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostOffice extends Model
{
    protected $table = 'post_office';

    protected $primaryKey = 'post_office_id';

    protected $fillable = [
        'post_office_name',
        'district_id',
    ];

    public $timestamps = false;

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function deviceExports()
    {
        return $this->hasMany(DeviceExport::class, 'post_office_id');
    }

}
