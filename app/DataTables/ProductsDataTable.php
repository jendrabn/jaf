<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProductsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.products.action')
            ->editColumn('image', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank"><img src="%s" width="50"></a>',
                    $row->image?->url,
                    $row->image?->preview_url
                );
            })
            ->editColumn('price', fn($row) => 'Rp ' . number_format((float) $row->price, 0, ',', '.'))
            ->editColumn('is_publish', function ($row) {
                return sprintf('<input type="checkbox" disabled %s />', $row->is_publish ? 'checked' : '');
            })
            ->setRowId('id')
            ->rawColumns(['action', 'image', 'is_publish']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        $model = $model->newQuery()
            ->with(['category', 'brand', 'media'])
            ->select('products.*');

        $filter_keys = ['product_category_id', 'product_brand_id', 'sex', 'is_publish'];

        foreach ($filter_keys as $key) {
            $model->when(request()->filled($key), fn($q) => $q->where($key, request($key)));
        }

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('dataTable-products')
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
                Button::make('filter'),
            ])
            ->ajax([
                'data' =>
                    'function (data) {
                        $.each($("#form-filter").serializeArray(), function (key, val) {
                           data[val.name] = val.value;
                        });
                    }'
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::checkbox('&nbsp;')->exportable(false)->printable(false)->width(35),
            Column::make('id')->title('ID'),
            Column::computed('image')->addClass('text-center'),
            Column::make('name'),
            Column::make('category.name', 'category.name')->title('Category'),
            Column::make('brand.name', 'brand.name')->title('Brand')->visible(false),
            Column::computed('sex_label', 'Gender')->visible(false),
            Column::make('price'),
            Column::make('stock'),
            Column::make('weight')->title('Weight (gram)')->visible(false),
            Column::computed('is_publish', 'Published')->visible(false),
            Column::make('sold_count')->searchable(false),
            Column::make('created_at')->visible(false),
            Column::make('updated_at')->visible(false),
            Column::computed('action', '&nbsp;')->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Products_' . date('dmY');
    }
}
