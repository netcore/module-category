<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Models\Menu;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $menus = [
            'leftAdminMenu' => [
                [
                    'name'   => 'Categories',
                    'icon'   => 'ion-pricetags',
                    'type'   => 'route',
                    'value'  => 'category::categories.index',
                    'active_resolver' => 'category::categories.*',
                    'module' => 'Category',
                    'parameters' => json_encode([])
                ]
            ]
        ];

        foreach( $menus as $name => $items ) {
            $menu = Menu::firstOrCreate([
                'name' => $name
            ]);

            foreach( $items as $item ){
                $i = $menu->items()->firstOrCreate($item);
                $i->is_active = 1;
                $i->save();
            }
        }
    }
}
