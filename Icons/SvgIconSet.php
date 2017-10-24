<?php

namespace Modules\Category\Icons;

class SvgIconSet implements IconSetInterface
{
    /**
     * Get array of available icons
     *
     * @return array
     */
    public function getIcons(): array
    {
        return [
            'icon-add-work' => 'icon-add-work',
            'icon-alert' => 'icon-alert'
        ];
    }

    /**
     * Get template for select2 render
     *
     * @return string
     */
    public function getSelect2Template(): string
    {
        return '<svg><use xlink:href="#::id::"></use></svg><span>::text::</span>';
    }

    /**
     * Get styles to inject
     *
     * @return array
     */
    public function getInjectableStyles(): array
    {
        return [];
    }

    /**
     * Get sprite to inject before container
     *
     * @return string
     */
    public function getInjectableSprite(): string
    {
        return view('sprites/svg-sprite')->render();
    }
}