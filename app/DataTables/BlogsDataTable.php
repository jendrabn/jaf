<?php

namespace App\DataTables;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BlogsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'admin.blogs.partials.action')
            ->editColumn('is_publish', 'admin.blogs.partials.action-published')
            ->editColumn('featured_image', function ($row) {
                return sprintf(
                    '<a href="%s" target="_blank"><img src="%s" width="50"></a>',
                    $row->featured_image?->url,
                    $row->featured_image?->preview_url
                );
            })
            ->editColumn('tags', function ($row) {
                $tags = [];

                $row->tags->each(function ($tag) use (&$tags) {
                    $tags[] = '<span class="badge badge-info rounded-0">' . $tag->name . '</span>';
                });

                return implode(' ', $tags);
            })
            ->setRowId('id')
            ->rawColumns(['is_publish', 'action', 'featured_image', 'tags']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Blog $model): QueryBuilder
    {
        $model = $model->newQuery()
            ->with(['author', 'category', 'tags', 'media'])
            ->select('blogs.*');

        $model->when(
            request()->filled('blog_category_id'),
            fn($q) => $q->where('blog_category_id', request('blog_category_id'))
        );
        $model->when(
            request()->filled('user_id'),
            fn($q) => $q->where('user_id', request('user_id'))
        );

        $model->when(
            request()->filled('blog_tag_id'),
            fn($q) => $q->whereHas('tags', fn($q) => $q->where('blog_tag_id', request('blog_tag_id')))
        );

        $model->when(
            request()->filled('is_publish'),
            fn($q) => $q->where('is_publish', request('is_publish'))
        );

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('blog-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
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
                Button::make('filter')
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
            Column::checkbox('&nbsp;')
                ->searchable(false)
                ->orderable(false)
                ->width(35),

            Column::make('id')
                ->title('ID'),

            Column::computed('featured_image')
                ->addClass('text-center'),

            Column::make('title'),

            Column::make('slug')
                ->visible(false),

            Column::make('author.name')
                ->title('Author'),

            Column::computed('is_publish', 'Published')
                ->addClass('text-center'),

            Column::make('category.name')
                ->title('Category'),

            Column::make('tags')
                ->title('Tag(s)')
                ->visible(false),

            Column::make('views_count'),

            Column::make('min_read')
                ->visible(false),

            Column::make('created_at')
                ->visible(false),

            Column::computed('action', 'Action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Blog_' . date('dmY');
    }
}
