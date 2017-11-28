<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Cache\RedisStore;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Category\Icons\IconSet;
use Modules\Category\Jobs\RegenerateCategoryFullSlugs;
use Modules\Category\Models\Category;
use Modules\Category\Http\Requests\CategoryRequest;

use Netcore\Translator\Helpers\TransHelper;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Support\Collection
     */
    public function index()
    {
        if (request()->wantsJson()) {
            return $this->getCategoriesTreeJson();
        }

        $icons = [
            'enabled'   => config('netcore.module-category.icons.enabled', false),
            'root_only' => config('netcore.module-category.icons.rootOnly', true),
        ];

        if ($icons['enabled']) {
            $icons['set'] = app('CategoryIconSet')->getIcons();
            $icons['template'] = app('CategoryIconSet')->getSelect2Template();
        }

        $jsVars = collect([
            'icons'     => $icons,
            'languages' => TransHelper::getAllLanguages(),
            'routes'    => [
                'index'  => route('category::categories.index'),
                'update' => route('category::categories.update', '--ID--'),
                'order'  => route('category::categories.order'),
            ],
        ]);

        return view('category::index', compact('jsVars'));
    }

    /**
     * Store category
     *
     * @param CategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create(
            $request->only('icon')
        );

        $category->storeTranslations(
            $request->input('translations')
        );

        if ($request->has('parent') && $parent = Category::find($request->input('parent'))) {
            $parent->appendNode($category);
        }

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Update category
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'icon' => $request->input('icon'),
        ]);

        $category->updateTranslations(
            $request->input('translations')
        );

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Delete category and it's descendants
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        $categories = Category::descendantsAndSelf($category->id);

        foreach ($categories as $item) {
            $item->delete();
        };

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Rebuild categories tree
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        Category::rebuildTree(
            $request->all()
        );

        $this->clearCache();

        dispatch(new RegenerateCategoryFullSlugs);

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Get data for JsTree
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCategoriesTreeJson()
    {
        $isTreeOpened = config('netcore.module-category.tree.opened_by_default', false);
        $suffixHelper = config('netcore.module-category.tree.name_suffix_helper_function', null);

        $categories = Category::defaultOrder()->get()->map(function (Category $category) use ($isTreeOpened, $suffixHelper) {
            $categoryName = trans_model($category, TransHelper::getLanguage(), 'name');

            if ($suffixHelper && function_exists($suffixHelper)) {
                $categoryName .= $suffixHelper($category);
            }

            return [
                'id'           => $category->id,
                'parent'       => $category->parent_id ?: '#',
                'text'         => $categoryName,
                'icon'         => $category->icon,
                'li_attr'      => [],
                'a_attr'       => [],
                'translations' => $category->translations,
                'state'        => [
                    'opened'   => $isTreeOpened,
                    'disabled' => false,
                    'selected' => false,
                ],
            ];
        });

        return $categories;
    }

    /**
     * Clear cache by tag
     *
     * @return void
     */
    private function clearCache() : void
    {
        $cacheTag = config('netcore.module-category.cache_tag');
        $isRedis = cache()->getStore() instanceof RedisStore;

        if ($cacheTag && $isRedis) {
            cache()->tags([$cacheTag])->flush();
        }  else {
            cache()->flush();
        }
    }
}
