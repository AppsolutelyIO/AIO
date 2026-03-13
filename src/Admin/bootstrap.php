<?php

declare(strict_types=1);

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;

/**
 * Dcat-admin - admin builder based on Laravel.
 *
 * @author jqh <https://github.com/jqhph>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 *
 * extend custom field:
 * Appsolutely\AIO\Form::extend('php', PHPEditor::class);
 * Appsolutely\AIO\Grid\Column::extend('php', PHPEditor::class);
 * Appsolutely\AIO\Grid\Filter::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 */
Grid::resolving(function (Grid $grid) {

    $grid->toolsWithOutline(false);

    $grid->disableViewButton(false);

    // $grid->rowSelector()->click();
    // $grid->model()->orderBy('id', 'DESC');

    $grid->filter(function (Grid\Filter $filter) {
        $filter->panel()->expand(false);
    });

    $grid->export();

    /*
    $grid->actions(function (Grid\Displayers\Actions $actions) {
        //$actions->disableView();
        $actions->disableEdit();
        //$actions->disableDelete();
    });
    */

    Admin::style('
    tr td.grid__actions__ a {
        display: block;
        margin-top: 5px;
        margin-bottom: 5px;
        min-width: 80px !important;
    }

    tr td.grid__actions__ a:hover span {
        text-decoration: underline;
    }

    tr td span.label {
        display: inline-block;
        padding: 6px;
    }
');

    Admin::script('
    $(document).ready(function() {
        // Auto-expand rows that have children (children count > 0)
        $("table.table.data-table tbody tr").each(function(index) {
            var $row = $(this);
            var $expandLink = $row.find("td a.grid-table-grid-load-children");

            if ($expandLink.length > 0) {
                // Find the children count cell directly using data-column attribute
                var $childrenCell = $row.find("td[data-column=\'children\']");
                var childrenText = $childrenCell.text().trim();
                var childrenCount = parseInt(childrenText);

                // If children count > 0, expand the row
                if (!isNaN(childrenCount) && childrenCount > 0) {
                    // Click the i tag inside the a tag
                    $expandLink.find("i.fa.fa-angle-right").click();
                }
            }
        });
    });
');

});
