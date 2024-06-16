<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DeviceReceipt extends Model
{
    protected $table = 'device_receipts';
    protected $primaryKey = 'receipt_id';

    protected $fillable = [
        'agency_id',
        'user_id',
        'receipt_date',
        'total_amount',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->user_id = Auth::id();
        });

        static::saving(function ($model) {
            $total = 0;
            foreach ($model->details as $detail) {
                $total += $detail->price;
            }
            $model->total_amount = $total;
        });
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'agency_id');
    }

    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(DeviceReceiptDetail::class, 'receipt_id', 'receipt_id');
    }
}
