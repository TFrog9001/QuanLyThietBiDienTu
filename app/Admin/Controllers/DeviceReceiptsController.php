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
        });


        // Adding print button
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();

            // $tools->append('<button id="printButton" class="btn btn-primary">In</button>');
        });

        // JavaScript for printing with embedded CSS
        // Admin::script(
        //     <<<JS

        //     $('#printButton').click(function() {
        //         var printContents = $('#app').html();  // Select the #app element to capture all content within it
        //         var css = `
        //             body {
        //                 font-family: Arial, sans-serif;
        //             }
        //             #app {
        //                 margin: 20px;
        //                 padding: 20px;
        //                 border: 1px solid #000;
        //                 max-width: 800px;
        //                 background: #fff;
        //             }
        //             .content-header h1 {
        //                 text-align: center;
        //                 margin-bottom: 20px;
        //             }
        //             .breadcrumb {
        //                 margin-bottom: 20px;
        //                 padding: 10px 0;
        //                 list-style: none;
        //                 background-color: transparent;
        //             }
        //             .card {
        //                 margin-bottom: 20px;
        //                 padding: 20px;
        //                 border: 1px solid #ccc;
        //                 background: #f9f9f9;
        //             }
        //             .card-header {
        //                 font-weight: bold;
        //                 font-size: 1.2em;
        //                 margin-bottom: 10px;
        //             }
        //             .form-horizontal .row {
        //                 margin-bottom: 10px;
        //             }
        //             .form-label {
        //                 font-weight: bold;
        //             }
        //             .show-value {
        //                 padding-left: 10px;
        //             }
        //             .table {
        //                 width: 100%;
        //                 margin-bottom: 20px;
        //                 border-collapse: collapse;
        //             }
        //             .table th,
        //             .table td {
        //                 border: 1px solid #000;
        //                 padding: 8px;
        //                 text-align: left;
        //             }
        //             .table th {
        //                 background: #f2f2f2;
        //             }
        //             .table-responsive {
        //                 overflow-x: auto;
        //             }
        //             footer {
        //                 display: flex;
        //                 justify-content: space-between;
        //                 align-items: center;
        //                 padding: 10px 0;
        //                 border-top: 1px solid #ccc;
        //             }
        //             .pagination {
        //                 margin: 0;
        //             }
        //             .pagination .page-item {
        //                 display: inline;
        //             }
        //             .pagination .page-link {
        //                 padding: 5px 10px;
        //                 margin: 0 2px;
        //                 border: 1px solid #ccc;
        //                 color: #000;
        //                 text-decoration: none;
        //             }
        //             #printButton {
        //                 display: block;
        //                 margin: 20px auto;
        //                 padding: 10px 20px;
        //                 background: #007bff;
        //                 color: #fff;
        //                 border: none;
        //                 cursor: pointer;
        //                 font-size: 1em;
        //             }
        //             #printButton:hover {
        //                 background: #0056b3;
        //             }
        //         `;
        //         var printWindow = window.open("", "", "width=800,height=600");
        //         printWindow.document.write('<html><head><title>Print Receipt</title><style>' + css + '</style></head><body>');
        //         printWindow.document.write(printContents);
        //         printWindow.document.write('</body></html>');
        //         printWindow.document.close();
        //         printWindow.print();
        //         printWindow.close();
        //     });

        // JS
        // );

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
