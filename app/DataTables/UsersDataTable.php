<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.users.action')
            ->editColumn('roles', function ($row) {
                $roles = [];

                $row->roles->each(function ($role) use (&$roles) {
                    $roles[] = '<span class="badge badge-info rounded-0">' . $role->name . '</span>';
                });

                return implode(' ', $roles);

            })
            ->editColumn('email', function ($row) {
                return sprintf(
                    '<a href="mailto:%s" class="text-body">%s</a>',
                    $row->email,
                    $row->email
                );
            })
            ->editColumn('phone', function ($row) {
                return sprintf(
                    '<a href="https://wa.me/%s" target="_blank" class="text-body">%s</a>',
                    $row->phone,
                    $row->phone
                );
            })
            ->setRowId('id')
            ->rawColumns(['action', 'roles', 'email', 'phone']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['roles'])
            ->select('users.*')
            ->withCount([
                'orders' => fn($q) => $q->where('status', Order::STATUS_COMPLETED)
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-users')
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
            ])


        ;
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

            Column::make('name'),

            Column::make('email'),

            Column::make('email_verified_at')
                ->visible(false),

            Column::make('roles', 'roles.name')
                ->orderable(false),

            Column::make('phone')
                ->title('Phone Number'),

            Column::make('sex_label', 'sex')
                ->title('Sex')
                ->visible(false),

            Column::make('birth_date')
                ->visible(false),

            Column::make('orders_count')
                ->searchable(false),

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
        return 'Users_' . date('dmY');
    }
}
