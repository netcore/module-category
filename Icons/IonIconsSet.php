<?php

namespace Modules\Category\Icons;

class IonIconsSet implements IconSetInterface
{
    /**
     * Get array of available icons
     *
     * @return array
     */
    public function getIcons(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/json/ion-icons.json'), true);
    }

    /**
     * Get template for select2 render
     *
     * @return string
     */
    public function getSelect2Template(): string
    {
        return '<i class="::id::"></i><span style="margin-left: 5px;">::text::</span>';
    }

    /**
     * Get styles to inject
     *
     * @return array
     */
    public function getInjectableStyles(): array
    {
        return [
            '//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'
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