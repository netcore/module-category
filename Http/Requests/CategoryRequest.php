<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Netcore\Translator\Helpers\TransHelper;

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
        $rules = [];
        $model = $this->route('category');

        foreach (TransHelper::getAllLanguages() as $language) {
            $transModel = null;

            if ($model) {
                $transModel = $model->translations()->where('locale', $language->iso_code)->first();
            }

            $rules['translations.' . $language->iso_code . '.name'] = 'required|min:3';
            $rules['translations.' . $language->iso_code . '.slug'] = 'nullable|unique:netcore_category__category_translations,slug' . ($transModel ? ',' . $transModel->id : '');
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
        $messages = [];
        $languages = TransHelper::getAllLanguages();

        foreach (TransHelper::getAllLanguages() as $language) {
            if (count($languages) == 1) {
                $messages['translations.' . $language->iso_code . '.name.required'] = 'Category name is required.';
                $messages['translations.' . $language->iso_code . '.name.min'] = 'Category name should contain at least 3 chars.';
                $messages['translations.' . $language->iso_code . '.slug.unique'] = 'Category slug should be unique.';
            } else {
                $messages['translations.' . $language->iso_code . '.name.required'] = 'Category name for language "' . $language->title . '" is required.';
                $messages['translations.' . $language->iso_code . '.name.min'] = 'Category name for language "' . $language->title . '" should contain at least 3 chars.';
                $messages['translations.' . $language->iso_code . '.slug.unique'] = 'Category slug for language "' . $language->title . '" should be unique.';
            }
        }

        return $messages;
    }
}
