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
        $export = DeviceExport::findOrFail($id);

        $show = new Show($export);

        $show->field('export_id', __('Export id'));
        $show->field('post_office_id', __('Post office id'));
        $show->field('postOffice.post_office_name', __('Post office'));
        $show->field('user_id', __('User id'))->as(function ($userId) {
            $user = AdminUser::findOrFail($userId);
            return $user ? $user->name . ' (ID: ' . $user->id . ')' : 'Unknown User';
        });
        $show->field('export_date', __('Export date'));

        $show->relation('deviceExportDetails', function ($grid) {
            $grid->column('device.device_name', __('Device Name'));
            $grid->column('device.serial_number', __('Serial Number'));
            $grid->column('device.device_type_id', __('Device Type'));
            $grid->column('device.warranty_expiry', __('Warranty Expiry'))->dateFormat('d-m-Y');
            $grid->setResource('/admin/devices');
            $grid->disableCreateButton();
            $grid->disableActions();
        });
        $postOfficeAdd = $export->postOffice->post_office_name .' - '.$export->postOffice->district->district_name;

        $exportDeviceHtml = '';
        $count = 1;

        foreach ($export->deviceExportDetails as $detail) {
            $exportDeviceHtml .= '<tr>';
            $exportDeviceHtml .= '<td style="border: 1px solid #000; padding: 8px;">' . $count++ . '</td>';
            $exportDeviceHtml .= '<td style="border: 1px solid #000; padding: 8px; text-align: left;">' . e($detail->device->device_name) . '</td>';
            $exportDeviceHtml .= '<td style="border: 1px solid #000; padding: 8px; text-align: left;">' . e($detail->device->serial_number) . '</td>';
            $exportDeviceHtml .= '<td style="border: 1px solid #000; padding: 8px; text-align: center;">' . date('d-m-Y', strtotime($detail->device->warranty_expiry)) . '</td>';
            $exportDeviceHtml .= '</tr>';
        }

        $show->panel()->tools(function ($tools) use ($export, $exportDeviceHtml) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();

            $tools->append('<button id="printExportButton" class="btn btn-primary">Print Handover Note</button>');
        });

        Admin::script(<<<JS
            document.getElementById("printExportButton").addEventListener("click", function() {
                var newWindowContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Trang In</title>
                        <style>
                            @media print {
                                body {
                                    font-family: "Times New Roman", Times, serif;
                                    margin: 0;
                                    padding: 0;
                                }
                                @page {
                                    margin: 0;
                                }
                                p {
                                    margin: 0px 0px 10px 0px;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div style="position: absolute; top: 300; left: 0; right: 0; text-align: center; z-index: 0;">
                            <img style="opacity: 0.3;" src="http://127.0.0.1:8000/storage/images/minilogo.png" alt="Logo">
                        </div>
                        <div style="position: relative; z-index: 1;">
                            <p style="font-size: 23px; font-weight: bold; text-align: center; padding-top: 50px; margin: 0px;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                            <p style="font-size: 21px; font-weight: bold; text-align: center; margin: 5px 0px 0px 0px;">Độc lập – Tự do – Hạnh phúc</p>
                            <p style="font-size: 23px; font-weight: bold; text-align: center; margin: 0px 0px 0px 0px;">-------------------------------------------</p>
                            <p style="font-size: 23px; font-weight: bold; text-align: center; margin: 30px 0px 30px 0px;">BIÊN BẢN GIAO NHẬN</p>
                            <p style="font-size: 18px; margin-left: 100px;">Hôm nay, ngày ... tháng ... năm ...</p>
                            <p style="font-size: 18px; font-weight: bold; margin-left: 100px;">BÊN A (BÊN GIAO, Phòng Kỹ Thuật Nghiệp Vụ): </p>
                            <div style="display:flex;">
                                <p style="font-size: 18px; margin-left: 100px;">- Ông / Bà: <input type="text" style="border-style:hidden; font-size: 18px; font-family: 'Times New Roman';" placeholder="Nhấn để nhập thông tin"/></p>
                                <p style="font-size: 18px; margin-left: 50px;">- Chức vụ: <input type="text" style="border-style:hidden; font-size: 18px; font-family: 'Times New Roman';" placeholder="Nhấn để nhập thông tin"/></p>
                            </div>
                            <p style="font-size: 18px; font-weight: bold; margin-left: 100px;">BÊN B (BÊN NHẬN, ${postOfficeAdd}): </p>
                            <div style="display:flex;">
                                <p style="font-size: 18px; margin-left: 100px;">- Ông / Bà: <input type="text" style="border-style:hidden; font-size: 18px; font-family: 'Times New Roman';" placeholder="Nhấn để nhập thông tin"/></p>
                                <p style="font-size: 18px; margin-left: 50px;">- Chức vụ: <input type="text" style="border-style:hidden; font-size: 18px; font-family: 'Times New Roman';" placeholder="Nhấn để nhập thông tin"/></p>
                            </div>
                            <p style="font-size: 18px; font-weight: bold; margin-left: 100px;">Nội dung:</p>
                            <p style="font-size: 18px; margin-left: 100px;">Hai bên cùng tiến hành bàn giao nhận thiết bị cụ thế như sau:</p>
                            <table style="width: 75%; border-collapse: collapse; text-align: center; margin: 0px 0px 0px 100px;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #000; padding: 8px;">STT</th>
                                        <th style="border: 1px solid #000; padding: 8px; text-align: center;">Tên thiết bị</th>
                                        <th style="border: 1px solid #000; padding: 8px; text-align: center;">Serial Number</th>
                                        <th style="border: 1px solid #000; padding: 8px; text-align: center;">Thời hạn bảo hành</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${exportDeviceHtml}
                                </tbody>
                            </table>
                            <p style="font-size: 18px; margin: 20px 0px 0px 100px;">Bên B đã nhận đủ số lượng và chất lượng đạt yêu cầu.</p>
                            <p style="font-size: 18px; margin: 0px 0px 50px 100px;">Biên bản được lập thành 03 bản có giá trị như nhau, bên A giữ 02 bản, bên B giữ 01 bản.</p>
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="font-size: 18px; font-weight: bold; text-align: center; padding-bottom: 150px;">XÁC NHẬN BÊN A</th>
                                        <th style="font-size: 18px; font-weight: bold; text-align: center; padding-bottom: 150px;">XÁC NHẬN BÊN B</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 18px; font-weight: bold; text-align: center;"></td>
                                        <td style="font-size: 18px; font-weight: bold; text-align: center;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </body>
                    </html>
                `;

                var newWindow = window.open("", "_blank");
                if (newWindow) {
                    newWindow.document.open();
                    newWindow.document.write(newWindowContent);
                    newWindow.document.close();
                } else {
                    alert("Popup blocked. Please allow popups for this site.");
                }
            });
        JS);

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

