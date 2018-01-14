<?php

namespace Modules\Category\Traits;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Http\Request;
use Modules\Category\Translations\CategoryTranslation;

trait ControllerHelpersTrait
{
    /**
     * Clear cache.
     *
     * @return void
     */
    private function clearCache(): void
    {
        try {
            $cacheTag = config('netcore.module-category.cache_tag');
            $isRedis = cache()->getStore() instanceof RedisStore;

            if ($cacheTag && $isRedis) {
                cache()->tags([$cacheTag])->flush();
            } else {
                cache()->flush();
            }
        } catch (Exception $exception) {
            logger()->error('[module-category] Unable to clear cache: ' . $exception->getMessage());
        }
    }

    /**
     * Check and modify custom slug.
     *
     * @param Request $request
     * @return void
     */
    private function modifySlugs(Request &$request): void
    {
        $translations = $request->input('translations', []);
        $existingSlugs = [];
        $i = 1;

        foreach ($translations as $iso => $translationData) {
            // Auto generated
            if (!$slug = array_get($translationData, 'slug')) {
                continue;
            }

            $slug = SlugService::createSlug(CategoryTranslation::class, 'slug', $slug, ['unique' => false]);

            // Prevent equal slug's on create.
            if (in_array($slug, $existingSlugs)) {
                $slug .= '-' . $i;
                $i++;
            }

            $translations[$iso]['slug'] = $slug;
            $existingSlugs[] = $slug;
        }

        $request->merge(compact('translations'));
    }
}