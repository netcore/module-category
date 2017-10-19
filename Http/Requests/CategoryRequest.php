<?php

namespace Modules\Category\Http\Requests;

use Netcore\Translator\Helpers\TransHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $languages = TransHelper::getAllLanguages();
        $category = $this->route('category');

        $rules = [];

        foreach ($languages as $language) {

            $translationInstance = null;
            $iso = $language->iso_code;

            if ($category) {
                $translationInstance = $category->translations()->where('locale', $language->iso_code)->first();
            }

            // Name
            $rules["translations.{$iso}.name"] = 'required|min:3';

            // Slug
            $rules["translations.{$iso}.slug"][] = 'nullable';
            $rules["translations.{$iso}.slug"][] = Rule::unique('netcore_category__category_translations', 'slug')->ignore(object_get($translationInstance, 'id'));
        }

        return $rules;
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'translations.*.name.required' => 'Category name :attribute is required.',
            'translations.*.name.min' => 'Category name :attribute should be at least 3 chars.',
            'translations.*.slug.unique' => 'Category slug :attribute should be unique.'
        ];
    }

    /**
     * Get the validation message attributes
     *
     * @return array
     */
    public function attributes()
    {
        $languages = TransHelper::getAllLanguages();
        $translatedAttributes = ['name', 'slug'];

        $placeholder = 'for language ":lang"';

        if ($languages->count() == 1) {
            $language = $languages->first();

            return [
                "translations.{$language->iso_code}.name" => '',
                "translations.{$language->iso_code}.slug" => '',
            ];
        }

        $attributes = [];

        foreach ($translatedAttributes as $translatedAttribute) {
            foreach ($languages as $language) {
                $attributes["translations.{$language->iso_code}.{$translatedAttribute}"] = str_replace(':lang', $language->title_localized, $placeholder);
            }
        }

        return $attributes;
    }
}
