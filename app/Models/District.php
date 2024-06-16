<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';

    protected $primaryKey = 'district_id';

    public $timestamps = false;

    public function postOffices()
    {
        return $this->hasMany(PostOffice::class, 'district_id', 'district_id');
    }
}
