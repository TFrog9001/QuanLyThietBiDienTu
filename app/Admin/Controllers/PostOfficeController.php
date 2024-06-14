<?php

namespace App\Admin\Controllers;

use OpenAdmin\Admin\Controllers\AdminController;
use OpenAdmin\Admin\Form;
use OpenAdmin\Admin\Grid;
use OpenAdmin\Admin\Show;
use App\Models\PostOffice;
use App\Models\District;

class PostOfficeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Post Office';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PostOffice());

        $grid->column('post_office_id', __('Post office ID'));
        $grid->column('post_office_name', __('Post office name'));
        $grid->column('district_id', __('District ID'));
        $grid->column('district.district_name', __('District name'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('district_id', 'District')->select(function () {
                return District::pluck('district_name', 'district_id');
            });

            $filter->like('post_office_name', 'Post Office');
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
        $show = new Show(PostOffice::findOrFail($id));

        $show->field('post_office_id', __('Post office ID'));
        $show->field('post_office_name', __('Post office name'));
        $show->field('district_id', __('District ID'))->as(function ($district_id) {
            $district = District::findOrFail($district_id);
            return $district->district_name;
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
        $form = new Form(new PostOffice());

        $form->text('post_office_id', __('Post office ID'));
        $form->text('post_office_name', __('Post office name'));

        // Dropdown để chọn quận/huyện từ bảng District
        $form->select('district_id', __('District'))->options(function () {
            return District::pluck('district_name', 'district_id');
        });

        return $form;
    }
}