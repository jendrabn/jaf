@extends('layouts.admin', ['title' => 'Show Blog'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Show Blog</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.blogs.index') }}">Back to list</a>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <tbody>

                        <tr>
                            <th>Featured Image</th>
                            <td class="text-center">
                                <a href="{{ $blog->featured_image->url }}"
                                   target="_blank">
                                    <img class="img-fluid w-100 border border-1"
                                         src="{{ $blog->featured_image->url }}" />
                                </a>
                                <br />

                                <p class="mb-0 text-muted">{{ $blog->featured_image_description }}</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td>{{ $blog->title }}</td>
                        </tr>

                        <tr>
                            <th>Slug</th>
                            <td>{{ $blog->slug }}</td>
                        </tr>

                        <tr>
                            <th>Author</th>
                            <td>{{ $blog->author?->name }}</td>
                        </tr>

                        <tr>
                            <th>Category Name</th>
                            <td>{{ $blog->category->name }}</td>
                        </tr>

                        <tr>
                            <th>Tag(s)</th>
                            <td>{{ $blog->tags->pluck('name')->implode(', ') }}</td>
                        </tr>

                        <tr>
                            <th>Min. Read</th>
                            <td>{{ $blog->min_read }}</td>
                        </tr>

                        <tr>
                            <th>Content</th>
                            <td>{!! $blog->content !!}</td>
                        </tr>

                        <tr>
                            <th>Published</th>
                            <td>{{ $blog->is_publish ? 'Yes' : 'No' }}</td>
                        </tr>

                        <tr>
                            <th>Created at</th>
                            <td>{{ $blog->created_at }}</td>
                        </tr>

                        <tr>
                            <th>Updated at</th>
                            <td>{{ $blog->updated_at }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
