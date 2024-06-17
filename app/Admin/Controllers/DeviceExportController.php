<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use OpenAdmin\Admin\Facades\Admin;

use App\Models\DeviceExport;
use App\Models\District;
use App\Models\PostOffice;
use App\Models\Device;
use App\Models\DeviceExportDetail;
use App\Models\AdminUser;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceExportController extends AdminController
{
    protected $title = 'DeviceExport';

    protected function grid()
    {
        $grid = new Grid(new DeviceExport());

        $grid->column('export_id', __('Export id'));
        $grid->column('post_office_id', __('Post office id'));
        $grid->column('postOffice.post_office_name', __('Post office'));
        $grid->column('user_id', __('User'))->display(function ($userId) {
            $user = AdminUser::find($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $grid->column('export_date', __('Export date'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('post_office_id', 'PostOffice')->select(function () {
                return PostOffice::pluck('post_office_name', 'post_office_id');
            });

            $filter->like('post_office_name', 'Post Office');
        });

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

    protected function detail($id)
    {
        $show = new Show(DeviceExport::findOrFail($id));

        $show->field('export_id', __('Export id'));
        $show->field('post_office_id', __('Post office id'));
        $show->field('user_id', __('User id'));
        $show->field('export_date', __('Export date'));

        $show->relation('details', function ($grid) {
            // $grid = new Grid(new Device());
            // Use the device relation within the details to display the device information
            $grid->column('device.device_name', __('Device Name'));
            $grid->column('device.serial_number', __('Serial Number'));
            $grid->column('device.device_type_id', __('Device Type'));
            $grid->column('device.warranty_expiry', __('Warranty Expiry'))->dateFormat('d-m-Y');
            $grid->setResource('/admin/devices');
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new DeviceExport());

        $currentUser = Auth::user();
        $form->display('user', __('User'))->default($currentUser->name . ' (ID: ' . $currentUser->id . ')')->readonly();
        $form->hidden('user_id')->default($currentUser->id);

        $form->select('district_id', __('District'))
            ->options(District::pluck('district_name', 'district_id'))
            ->load('post_office_id', '/api/post-offices');

        $form->select('post_office_id', __('Post Office'));

        $form->date('export_date', __('Export Date'))->default(date('Y-m-d'))->format('Y-m-d');

        $form->listbox('device_details', __('Add Devices'))->options(function () {
            $devices = Device::where('state', 'available')->get();

            $options = [];
            foreach ($devices as $device) {
                $options[$device->device_id] = $device->serial_number . ' - ' . $device->device_name;
            }

            return $options;
        })->height(400)->required();

        return $form;
    }

    public function store()
    {
        $currentUser = Auth::user();
        $data = request()->all();

        $data['device_details'] = array_filter($data['device_details'], function ($value) {
            return $value !== null;
        });

        DB::transaction(function () use ($data, $currentUser) {
            // Create a new DeviceExport instance
            $deviceExport = new DeviceExport();
            $deviceExport->post_office_id = $data['post_office_id'];
            $deviceExport->user_id = $currentUser->id;
            $deviceExport->export_date = $data['export_date'];
            $deviceExport->save();

            // Save details to DeviceExportDetail and update device state
            foreach ($data['device_details'] as $deviceId) {
                $deviceExportDetail = new DeviceExportDetail();
                $deviceExportDetail->export_id = $deviceExport->export_id;
                $deviceExportDetail->device_id = $deviceId;
                $deviceExportDetail->save();

                // Update device state to 'disabled'
                $device = Device::find($deviceId);
                if ($device) {
                    $device->state = 'disabled';
                    $device->save();
                } else {
                    throw new \Exception("Device with ID {$deviceId} not found");
                }
            }
        });

        admin_toastr(__('Device Export created successfully'), 'success');

        return redirect('/admin/device-exports');
    }

    public function getPostOffices(Request $request)
    {
        $district_id = $request->input('query');
        $postOffices = PostOffice::where('district_id', $district_id)
            ->pluck('post_office_name', 'post_office_id');

        $data = $postOffices->map(function ($name, $id) {
            return [
                'id' => $id,
                'text' => $name,
            ];
        })->values()->toArray();

        return $data;
    }

}

