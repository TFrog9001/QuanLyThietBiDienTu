<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\Device;

use App\Models\DeviceType;

class DeviceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Device';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Device());

        $grid->column('device_id', __('Device id'));
        $grid->column('serial_number', __('Serial number'));
        $grid->column('device_name', __('Device name'));
        $grid->column('type.device_type_name', __('Type'));
        $grid->column('warranty_expiry', __('Warranty expiry'));
        $grid->column('state', __('State'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('device_type_id', 'DeviceType')->select(function () {
                return DeviceType::pluck('device_type_name', 'device_type_id');
            });

            $filter->like('serial_number', 'Serial Number');
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
        $show = new Show(Device::findOrFail($id));

        $show->field('device_id', __('Device id'));
        $show->field('serial_number', __('Serial number'));
        $show->field('device_name', __('Device name'));
        $show->field('device_type_id', __('Device type id'));
        $show->field('warranty_expiry', __('Warranty expiry'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Device());

        $form->text('serial_number', __('Serial number'));
        $form->text('device_name', __('Device name'));

        $form->select('device_type_id', __('Device type'))->options(DeviceType::pluck('device_type_name', 'device_type_id'));

        $form->date('warranty_expiry', __('Warranty expiry'))->default(date('Y-m-d'));

        return $form;
    }
}
