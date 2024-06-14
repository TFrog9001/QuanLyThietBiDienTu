<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\DeviceType;

class DeviceTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'DeviceType';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeviceType());

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('device_type_name', 'Device Type');
        });

        $grid->column('device_type_id', __('Device type ID'));
        $grid->column('device_type_name', __('Device type'));

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
        $show = new Show(DeviceType::findOrFail($id));

        $show->field('device_type_id', __('Device type ID'));
        $show->field('device_type_name', __('Device type name'));


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DeviceType());

        $form->text('device_type_name', __('Divice Name'));


        return $form;
    }
}
