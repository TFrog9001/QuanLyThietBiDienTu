<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use OpenAdmin\Admin\Facades\Admin;


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
    protected $title = 'Device Receipt';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeviceReceipt());

        $grid->column('receipt_id', __('Receipt ID'))->sortable();
        $grid->column('agency.agency_name', __('Agency'));
        $grid->column('user_id', __('User'))->display(function ($userId) {
            $user = AdminUser::find($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $grid->column('receipt_date', __('Receipt Date'))->dateFormat('d-m-Y');
        $grid->column('total_amount', __('Total Amount'));

        $grid->actions(function ($actions) {
            // Get the current logged-in admin user
            $currentUser = Admin::user();

            // Check if the current user is the creator of this record
            if ($actions->row->user_id != $currentUser->id) {
                // Disable all actions
                $actions->disableDelete();
                $actions->disableEdit();
                // $actions->disableView();
            }
        });

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
        // Eager load the details and the related devices
        $receipt = DeviceReceipt::findOrFail($id);

        $show = new Show($receipt);

        // Hiển thị thông tin biên nhận thiết bị
        $show->field('receipt_id', __('Receipt ID'));
        $show->field('agency.agency_name', __('Agency'));
        $show->field('user_id', __('User'))->as(function ($userId) {
            $user = AdminUser::find($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $show->field('receipt_date', __('Receipt Date'))->date('d-m-Y');
        $show->field('total_amount', __('Total Amount'));

        // Hiển thị thông tin chi tiết thiết bị

        $show->relation('details', function ($grid) {

            $grid->column('device.device_name', __('Device Name'));
            $grid->column('device.serial_number', __('Serial Number'));
            $grid->column('device.device_type_id', __('Device Type'));
            $grid->column('device.warranty_expiry', __('Warranty Expiry'))->dateFormat('d-m-Y');
            $grid->column('price', __('Price'));
            $grid->column('device.state', __('State'));
            $grid->setResource('/admin/devices');
            $grid->disableCreateButton();
            $grid->disableActions();
        });

        $show->panel()->tools(function ($tools){
            $tools->disableEdit();
            // $tools->disableList();
            // $tools->disableDelete();
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

        if ($form->isCreating()) {
            $currentUser = Auth::user();
            $form->display('user', __('User'))->default($currentUser->name . ' (ID: ' . $currentUser->id . ')')->readonly();
            $form->hidden('user_id')->default($currentUser->id);

            $form->select('agency_id', __('Agency'))
                ->options(Agency::all()->pluck('agency_name', 'agency_id'))
                ->required();

            $form->date('receipt_date', __('Receipt Date'))->default(date('Y-m-d'))->required();

            $form->hasMany('details', __('Device Receipt Details'), function (Form\NestedForm $form) {
                $form->text('serial_number', __('Serial Number'))->required();
                $form->text('device_name', __('Device Name'))->required();
                $form->select('device_type_id', __('Device Type'))
                    ->options(DeviceType::orderBy('device_type_id', 'asc')->pluck('device_type_name', 'device_type_id'))
                    ->required();
                $form->date('warranty_expiry', __('Warranty Expiry'))->required();
                $form->currency('price', __('Price'))->symbol('₫')->required();
            });
        } else {
            $form->select('agency_id', __('Agency'))
                ->options(Agency::all()->pluck('agency_name', 'agency_id'))
                ->required();

            $form->date('receipt_date', __('Receipt Date'))->default(date('Y-m-d'))->required();

            $form->hasMany('details', __('Device Receipt Details'), function (Form\NestedForm $form) {
                $form->text('serial_number', __('Serial Number'))->required();
                $form->text('device_name', __('Device Name'))->required();
                $form->select('device_type_id', __('Device Type'))
                    ->options(DeviceType::orderBy('device_type_id', 'asc')->pluck('device_type_name', 'device_type_id'))
                    ->required();
                $form->date('warranty_expiry', __('Warranty Expiry'))->required();
                $form->currency('price', __('Price'))->symbol('₫')->required();
            });
        }
        return $form;
    }

    public function store()
    {
        DB::beginTransaction();

        try {
            $data = request()->all();
            $details = $data['details'];
            unset($data['details']);

            $totalAmount = 0;

            foreach ($details as $detail) {
                $totalAmount += (float) str_replace(',', '', $detail['price']);
            }

            $data['total_amount'] = $totalAmount;

            $receipt = DeviceReceipt::create($data);

            foreach ($details as $detail) {
                $device = Device::create([
                    'serial_number' => $detail['serial_number'],
                    'device_name' => $detail['device_name'],
                    'device_type_id' => $detail['device_type_id'],
                    'warranty_expiry' => $detail['warranty_expiry'],
                ]);

                DeviceReceiptDetail::create([
                    'device_id' => $device->device_id,
                    'receipt_id' => $receipt->receipt_id,
                    'price' => str_replace(',', '', $detail['price']),
                ]);
            }

            DB::commit();
            return redirect('/admin/device-receipts')->withSuccess('success', 'Device receipt created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'An error occurred while saving the device receipt. Please try again.']);
        }
    }

}
