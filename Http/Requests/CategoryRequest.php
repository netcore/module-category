<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Validation\Rule;
use Netcore\Translator\Helpers\TransHelper;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $languages = TransHelper::getAllLanguages();
        $category = $this->route('category');

        $rules = [
            'icons.*' => 'image|max:4096',
        ];

        foreach ($languages as $language) {

            $translationInstance = null;
            $iso = $language->iso_code;

            if ($category) {
                $translationInstance = $category->translations()->where('locale', $language->iso_code)->first();
            }

            // Name
            $rules["translations.{$iso}.name"] = 'required';

            // Slug
            $uniqueSlug = Rule::unique('netcore_category__category_translations', 'slug')->ignore(
                object_get($translationInstance, 'id')
            );

            $rules["translations.{$iso}.slug"][] = 'nullable';
            $rules["translations.{$iso}.slug"][] = $uniqueSlug;
        }

        return $rules;
    }

    /**
     * Get the validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'translations.*.name.required' => 'Category name :attribute is required.',
            'translations.*.slug.unique'   => 'Category slug :attribute should be unique.',
        ];
    }

    /**
     * Get the validation message attributes
     *
     * @return array
     */
    public function attributes(): array
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
                $replaced = str_replace(':lang', $language->title_localized, $placeholder);
                $attributes["translations.{$language->iso_code}.{$translatedAttribute}"] = $replaced;
            }
        }

        return $attributes;
    }
}
