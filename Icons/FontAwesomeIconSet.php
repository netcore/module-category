<?php

namespace Modules\Category\Icons;

class FontAwesomeIconSet implements IconSetInterface
{
    /**
     * Get array of available icons
     *
     * @return array
     */
    public function getIcons(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/json/font-awesome.json'), true);
    }

    /**
     * Get template for select2 render
     *
     * @return string
     */
    public function getSelect2Template(): string
    {
        return '<i class="::text::"></i><span style="margin-left: 5px;">::text::</span>';
    }

    /**
     * Get styles to inject
     *
     * @return array
     */
    public function getInjectableStyles(): array
    {
        return [
            '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'
        ];
    }

    /**
     * Get sprite to inject before container
     *
     * @return string
     */
    public function getInjectableSprite(): string
    {
        return '';
    }
}