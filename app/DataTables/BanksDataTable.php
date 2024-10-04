<?php

namespace App\DataTables;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BanksDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.banks.action')
            ->editColumn('logo', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank"><img src="%s" width="50"></a>',
                    $row->logo?->url,
                    $row->logo?->preview_url
                );
            })
            ->setRowId('id')
            ->rawColumns(['action', 'logo']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Bank $model): QueryBuilder
    {
        return $model->newQuery()->with(['media']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-banks')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->selectStyleMultiShift()
            ->selectSelector('td:first-child')
            ->buttons([
                Button::make('create'),
                Button::make('selectAll'),
                Button::make('selectNone'),
                Button::make('excel'),
                Button::make('reset'),
                Button::make('reload'),
                Button::make('colvis'),
                Button::make('bulkDelete'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::checkbox('&nbsp;')
                ->exportable(false)
                ->printable(false)
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::computed('logo')
                ->addClass('text-center'),

            Column::make('name')
                ->title('Bank Name'),

            Column::make('code')
                ->title('Bank Code'),

            Column::make('account_name'),

            Column::make('account_number'),

            Column::make('created_at')
                ->visible(false),

            Column::computed('action', 'Action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Banks_' . date('dmY');
    }
}
