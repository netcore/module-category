@extends('admin::layouts.master')

@section('styles')
    @if($categoryGroup->hasPresenter())
        @foreach($categoryGroup->getPresenter()->getInjectableStyles() as $style)
            <link rel="stylesheet" href="{{ $style }}">
        @endforeach
    @endif

    <style type="text/css">
        .select2-container svg {
            width: 15px;
            height: 15px;
            margin-right: 10px;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/category/admin/css/jstree-themes/default/style.min.css') }}">
@endsection

@section('scripts')
    <script type="text/javascript">
        var categoryModule = {
            languages: {!! $languages !!},
            categoryGroup: {!! $categoryGroup !!},
            routes: {
                fetch:   '{{ route('category::categories.fetch', $categoryGroup) }}',
                order:   '{{ route('category::categories.order', $categoryGroup) }}',
                store:   '{{ route('category::categories.store', $categoryGroup) }}',
                update:  '{{ route('category::categories.update', [$categoryGroup, '-ID-']) }}',
                destroy: '{{ route('category::categories.destroy', [$categoryGroup, '-ID-']) }}',
            },

            @if($categoryGroup->hasPresenter())
            icons: {
                template: '{!! $categoryGroup->getPresenter()->getSelect2Template() !!}',
                options: {!! json_encode($categoryGroup->getPresenter()->getIcons()) !!}
            }
            @endif
        };
    </script>
    <script src="{{ asset('assets/category/admin/js/jstree.js') }}"></script>
    <script src="{{ asset('assets/category/admin/js/block-ui.js') }}"></script>
    <script src="{{ versionedAsset('assets/category/admin/js/categories.js') }}"></script>
@endsection

@section('content')
    {!! Breadcrumbs::render('admin.categories') !!}

    <div class="page-header">
        <h1><span><i class="page-header-icon ion-pricetags"></i> Categories</span></h1>
    </div>

    @if($categoryGroup->hasPresenter())
        <div style="display: none !important;">
            {!! $categoryGroup->getPresenter()->getInjectableSprite() !!}
        </div>
    @endif

    <ul class="nav nav-tabs">
        @foreach($categoryGroups as $group)
            <li role="presentation" class="{{ $categoryGroup->id == $group->id ? 'active' : '' }}">
                <a href="{{ route('category::categories.index', ['group' => $group->id]) }}">
                    {{ $group->title }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="panel b-t-0" id="categoryApp" v-cloak>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <categories-tree></categories-tree>
                </div>

                <div class="col-md-6">
                    <categories-form></categories-form>
                </div>
            </div>
        </div>
    </div>
@stop
