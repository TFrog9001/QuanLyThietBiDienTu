<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;


use \App\Models\DeviceReceipt;

use App\Models\Device;
use App\Models\Agency;
use App\Models\AdminUser;
use App\Models\DeviceType;
use App\Models\DeviceReceiptDetail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceReceiptsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DeviceReceipt';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeviceReceipt());

        $grid->column('receipt_id', __('Receipt ID'));
        $grid->column('agency.agency_name', __('Agency'));
        $grid->column('user.name', __('User'))->display(function ($userId) {
            $user = AdminUser::find($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $grid->column('receipt_date', __('Receipt Date'));
        $grid->column('total_amount', __('Total Amount'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(DeviceReceipt::findOrFail($id));

        $show->field('receipt_id', __('Receipt ID'));
        $show->field('agency.agency_name', __('Agency'));
        $show->field('user.name', __('User'))->as(function ($userId) {
            $user = AdminUser::find($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $show->field('receipt_date', __('Receipt Date'));
        $show->field('total_amount', __('Total Amount'));

        $show->relation('details', function ($grid) {
            $grid->device_id('Device')->display(function ($device_id) {
                return Device::find($device_id)->device_name;
            });
            $grid->quantity('Quantity');
            $grid->price('Price');
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DeviceReceipt());

        $form->select('agency_id', __('Agency'))
            ->options(Agency::all()->pluck('agency_name', 'agency_id'))
            ->required();
        $form->number('user_id', __('User ID'))->required();
        $form->date('receipt_date', __('Receipt Date'))
            ->default(date('Y-m-d'))
            ->required();
        $form->decimal('total_amount', __('Total Amount'))->required();

        $form->hasMany('details', __('Devices'), function (Form\NestedForm $form) {
            $form->text('serial_number', __('Serial Number'))->required();
            $form->text('device_name', __('Device Name'))->required();
            $form->select('device_type_id', __('Device Type'))
                ->options(DeviceType::all()->pluck('device_type_name', 'device_type_id'))
                ->required();
            $form->date('warranty_expiry', __('Warranty Expiry'))->required();
            $form->decimal('price', __('Price'))->required();
        });

        $form->saving(function (Form $form) {
            unset($form->model()->total_amount);
        });

        $form->saved(function (Form $form) {
            $receipt = $form->model();

            // Xóa các chi tiết phiếu nhập hiện tại
            $receipt->details()->delete();

            foreach ($form->details as $detail) {
                // Lưu thiết bị trước
                $device = new Device();
                $device->serial_number = $detail['serial_number'];
                $device->device_name = $detail['device_name'];
                $device->device_type_id = $detail['device_type_id'];
                $device->warranty_expiry = $detail['warranty_expiry'];
                $device->save();

                // Kiểm tra xem device đã có device_id chưa
                if (!$device->device_id) {
                    throw new \Exception('Failed to save the device.');
                }

                // Debug thông tin thiết bị
                \Log::info('Device saved:', ['device_id' => $device->device_id]);

                // Sau đó lưu chi tiết phiếu nhập
                $receiptDetail = new DeviceReceiptDetail();
                $receiptDetail->receipt_id = $receipt->id;
                $receiptDetail->device_id = $device->device_id; // Sử dụng đúng trường device_id
                $receiptDetail->price = $detail['price'];
                $receiptDetail->save();

                // Debug thông tin chi tiết phiếu nhập
                \Log::info('DeviceReceiptDetail saved:', ['receipt_id' => $receiptDetail->receipt_id, 'device_id' => $receiptDetail->device_id, 'price' => $receiptDetail->price]);
            }
        });


        return $form;
    }

}
