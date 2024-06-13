<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\District;

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

        $grid->filter(function($filter){
            // Xóa bộ lọc mặc định theo ID
            $filter->disableIdFilter();

            $filter->like('district_id', 'District ID');
            // Bộ lọc theo tên quận/huyện
            $filter->like('district_name', 'District Name');
        });

        $grid->column('district_id', __('District ID'));
        $grid->column('district_name', __('District name'));

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
