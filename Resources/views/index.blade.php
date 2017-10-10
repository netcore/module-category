@extends('admin::layouts.master')

@section('styles')
    <style type="text/css">
        .color-red {
             color: red;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ versionedAsset('assets/category/admin/js/categories.js') }}"></script>
@endsection

@section('content')

    <div class="page-header">
        <h1>
            <span>
                <i class="page-header-icon ion-pricetags"></i> Categories
            </span>
        </h1>
    </div>

    <div class="panel" id="categoryApp">
        <div class="panel-heading">Categories list</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <categories-tree></categories-tree>
                </div>
                <div class="col-md-6" id="categoryForm">
                    <form action="#">

                        <div class="form-group">
                            <label for="name-lv">Category name: <span class="color-red">*</span></label>
                            <input type="text" id="name-lv" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="slug-lv">Category slug:</label>
                            <input type="text" id="slug-lv" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="icon">Icon:</label>
                            <select id="icon" class="form-control"></select>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
