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
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData(): array
    {
        $this->modifySlugs();

        return $this->all();
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

        $rules = [];

        foreach ($languages as $language) {
            $translationInstance = null;
            $iso = $language->iso_code;

            $uniqueRule = Rule::unique('netcore_category__category_translations', 'slug');

            if ($category) {
                $translationInstance = $category->translations()->where('locale', $language->iso_code)->first();
                $uniqueRule->ignore($translationInstance->id);
            }

            $rules["translations.{$iso}.name"] = 'required';
            $rules["translations.{$iso}.slug"] = ['nullable', $uniqueRule];
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
                $attributes["translations.{$language->iso_code}.{$translatedAttribute}"] = str_replace(':lang', $language->title_localized, $placeholder);
            }
        }

        return $attributes;
    }

    /**
     * Modify custom slugs.
     *
     * @return void
     */
    public function modifySlugs(): void
    {
        $data = $this->all();

        foreach (array_get($data, 'translations', []) as $iso => $translationsData) {
            if ($slug = array_get($translationsData, 'slug')) {
                $data['translations'][$iso]['slug'] = str_slug($slug);
            }
        }

        $this->merge($data);
    }
}
