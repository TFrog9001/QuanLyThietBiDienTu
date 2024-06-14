<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use \App\Models\Agency;

class AgencyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Agency';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Agency());

        $grid->filter(function($filter){
            $filter->disableIdFilter();

            $filter->like('agency_name', 'Agency name');
        });

        $grid->column('agency_id', __('Agency id'));
        $grid->column('agency_name', __('Agency name'));

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
        $show = new Show(Agency::findOrFail($id));

        $show->field('agency_id', __('Agency id'));
        $show->field('agency_name', __('Agency name'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Agency());

        $form->text('agency_name', __('Agency name'));

        return $form;
    }
}
