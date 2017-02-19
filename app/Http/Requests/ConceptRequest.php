<?php

namespace Knowfox\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Knowfox\Models\Concept;
use Illuminate\Validation\Rule;

class ConceptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $concept = $this->route('concept');
        return !$concept || $this->user()->can('update', $concept);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $title_rule = [
            'required',
            'max:255',
        ];

        $concept = $this->route('concept');
        if ($concept) {
            $title_rule[] = Rule::unique('concepts')->ignore($concept->id);
        }

        return [
            'title' => $title_rule,
        ];
    }
}
