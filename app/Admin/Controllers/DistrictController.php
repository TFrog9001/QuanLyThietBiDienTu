<?php

namespace App\Admin\Controllers;

use App\Models\DeviceExport;

use Illuminate\Support\Facades\DB;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;

use \App\Models\District;
use App\Models\PostOffice;
use App\Models\Device;


class DistrictController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'District';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new District());

        $grid->filter(function ($filter) {
            // Xóa bộ lọc mặc định theo ID
            $filter->disableIdFilter();

            $filter->like('district_id', 'District ID');
            // Bộ lọc theo tên quận/huyện
            $filter->like('district_name', 'District Name');
        });

        $grid->column('district_id', __('District ID'));
        $grid->column('district_name', __('District name'));

        $grid->model()->leftJoin('post_office', 'district.district_id', '=', 'post_office.district_id')
            ->leftJoin('device_exports', 'post_office.post_office_id', '=', 'device_exports.post_office_id')
            ->leftJoin('device_export_details', 'device_exports.export_id', '=', 'device_export_details.export_id')
            ->leftJoin('devices', 'device_export_details.device_id', '=', 'devices.device_id')
            ->select('district.district_id', 'district.district_name', DB::raw('COUNT(devices.device_id) as devices_count'))
            ->groupBy('district.district_id', 'district.district_name');

        $grid->column('devices_count', __('Devices Count'))->display(function ($devices_count) {
            return $devices_count;
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
        $show = new Show(District::findOrFail($id));

        $show->field('district_id', __('District ID'));
        $show->field('district_name', __('District name'));

        $show->relation('exportedDevices', function ($grid) use ($id) {
            $grid->model()->select('devices.*', 'post_office.*', 'device_exports.export_date')
                ->join('device_export_details', 'device_export_details.export_id', '=', 'device_exports.export_id')
                ->join('devices', 'devices.device_id', '=', 'device_export_details.device_id');

            $grid->column('device_id', 'ID');
            $grid->column('serial_number', 'Serial Number');
            $grid->column('device_name', 'Device Name');
            $grid->column('export_date', 'Export Date')->dateFormat('d-m-Y');
            $grid->column('post_office_name', 'Post Office');

            $grid->disableCreateButton();
            $grid->disableActions();
        });

        $show->relation('postOffices', function ($grid) {
            $grid->model()->orderBy('post_office_id', 'asc');
            $grid->column('post_office_id', __('Post office ID'));
            $grid->column('post_office_name', __('Post office name'));
            $grid->setResource('/admin/post-offices');

            $grid->actions(function ($actions) {
                $actions->disableDelete(false);
                $actions->disableEdit(false);
                $actions->disableView(false);
            });
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
        $form = new Form(new District());

        $form->text('district_id', __('District ID'));
        $form->text('district_name', __('District name'));

        return $form;
    }
}
