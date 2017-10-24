<?php

namespace Modules\Category\Icons;

interface IconSetInterface
{
    /**
     * Get array of available icons
     *
     * @return array
     */
    public function getIcons(): array;

    /**
     * Get template for select2 render
     *
     * @return string
     */
    public function getSelect2Template(): string;

    /**
     * Get styles to inject
     *
     * @return array
     */
    public function getInjectableStyles(): array;

    /**
     * Get sprite to inject before container
     *
     * @return string
     */
    public function getInjectableSprite(): string;

}