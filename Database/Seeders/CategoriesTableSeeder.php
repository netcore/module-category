<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Netcore\Translator\Helpers\TransHelper;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('netcore_category__categories')->delete();

        $categories = [
            [
                'name'     => 'Root Category #1',
                'children' => [
                    ['name' => 'Child Category #1.1'],
                    ['name' => 'Child Category #1.2'],
                    ['name' => 'Child Category #1.3'],
                    [
                        'name'     => 'Child Category #1.4',
                        'children' => [
                            ['name' => 'Child Category #1.4.1'],
                            ['name' => 'Child Category #1.4.2'],
                            ['name' => 'Child Category #1.4.3'],
                        ],
                    ],
                ],
            ],

            [
                'name'     => 'Root Category #2',
                'children' => [
                    ['name' => 'Child Category #2.1'],
                    ['name' => 'Child Category #2.2'],
                    ['name' => 'Child Category #2.3'],
                    [
                        'name'     => 'Child Category #2.4',
                        'children' => [
                            ['name' => 'Child Category #2.4.1'],
                            ['name' => 'Child Category #2.4.2'],
                            ['name' => 'Child Category #2.4.3'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $category) {
            $this->createCategory($category);
        }
    }

    /**
     * Create category
     *
     * @param array $categoryData
     * @param null|Category $parentCategory
     * @return void
     */
    private function createCategory(array $categoryData, $parentCategory = null)
    {
        $category = Category::create([]);

        // Store translations
        foreach (TransHelper::getAllLanguages() as $language) {
            $category->translations()->create([
                'locale' => $language->iso_code,
                'name'   => $categoryData['name'],
                'slug'   => str_slug($categoryData['name']),
            ]);
        }

        // Set parent category
        if ($parentCategory) {
            $category->appendToNode($parentCategory)->save();
        }

        // Create child nodes
        if (isset($categoryData['children'])) {
            foreach ($categoryData['children'] as $child) {
                $this->createCategory($child, $category);
            }
        }
    }

}