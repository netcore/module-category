@extends('admin::layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/category/admin/css/jstree-themes/default/style.min.css') }}">
@endsection

@section('scripts')
    <script type="text/javascript">
        window.categoryModule = {
            languages: {!! $languages->toJson() !!},
            routes: {
                index: '{{ route('category::categories.index') }}',
                update: '{{ route('category::categories.update', '--ID--') }}'
            }
        };
    </script>

    <script src="{{ asset('assets/category/admin/js/jstree.js') }}"></script>
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
                    <categories-tree route="{{ route('category::categories.index') }}"></categories-tree>
                </div>

                <div class="col-md-6" id="categoryForm">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="panel-title">@{{ categoryFormAction === 'edit' ? 'Edit' : 'Create' }} category</span>
                            <div class="panel-heading-controls" v-if="categoryFormAction === 'edit'">
                                <button class="btn btn-xs btn-success" @click="__categoryApp__addChildToCategory">
                                    <i class="fa fa-plus-circle"></i> Add child
                                </button>

                                <button class="btn btn-xs btn-warning" @click="__categoryApp__cancelCategoryEditing">
                                    <i class="fa fa-times-circle"></i> Cancel editing
                                </button>

                                <button class="btn btn-xs btn-danger" @click="__categoryApp__deleteCategory">
                                    <i class="fa fa-trash"></i> Delete category
                                </button>
                            </div>
                        </div>

                        <div class="panel-body">
                            <template v-if="_.size(languages) > 1">
                                <ul class="nav nav-tabs">
                                    <li v-for="(language, iso) in languages">
                                        <a data-toggle="tab" :href="'#translations-' + iso">@{{ language.title }}</a>
                                    </li>
                                </ul>
                            </template>

                            <div class="form-group" v-if="showSelectedParentCategory">
                                <label for="selectedNodeName">Parent category:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" v-model="selectedNodeName" disabled id="selectedNodeName">
                                    <div class="input-group-btn">
                                        <button class="btn btn-danger" @click="selectedNode = null">
                                        <i class="fa fa-times"></i> Create as root
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-content">
                                <div v-for="(language, iso) in languages" :id="'translations-' + iso" class="tab-pane fade in active">
                                    <div class="form-group">
                                        <label :for="'name-' + iso">Category name: <span class="color-red">*</span></label>
                                        <input type="text" :id="'name-' + iso" class="form-control" v-model="categoryForm.translations[iso].name">
                                    </div>

                                    <div class="form-group">
                                        <label :for="'slug-' + iso">Category slug:</label>
                                        <input type="text" :id="'slug-' + iso" class="form-control" v-model="categoryForm.translations[iso].slug">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="icon">Icon:</label>
                                <select id="icon" class="form-control" v-model="categoryForm.icon"></select>
                            </div>
                        </div>

                        <div class="panel-footer text-right">
                            <button type="button" @click="__categoryApp__saveCategory($event)" class="btn btn-success">
                                <span v-if="categoryFormAction === 'edit'"><i class="fa fa-save"></i> Save</span>
                                <span v-else><i class="fa fa-plus-circle"></i> Create</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
