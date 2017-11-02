<?php

namespace Modules\Category\Repositories;

use Illuminate\Support\Collection;
use Modules\Category\Models\Category;
use Netcore\Translator\Helpers\TransHelper;

class CategoryRepository
{
    /**
     * Get plain categories
     * Used to build full slugs without querying the database
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getCategoriesPlain() : Collection
    {
        static $categories;

        if ($categories) {
            return $categories;
        }

        $categories = [];
        $languages = TransHelper::getAllLanguages();

        foreach (Category::all() as $category) {
            $categoryData = [
                'id'        => $category->id,
                'parent_id' => $category->parent_id,
                'slugs'     => [],
            ];

            foreach ($languages as $language) {
                $translation = $category->translations->where('locale', $language->iso_code)->first();
                $slug = object_get($translation, 'slug');

                $categoryData['slugs'][$language->iso_code] = $slug;
            }

            $categories[] = $categoryData;
        }

        $categories = collect($categories);

        return $categories;
    }
}