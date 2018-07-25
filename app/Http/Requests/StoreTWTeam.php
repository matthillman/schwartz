<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTWTeam extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->edit_tw;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'bail|required',
            'aliases' => 'string',
            'counter'=> 'size:'.count($this->get('notes', [])),
            'notes'=> 'size:'.count($this->get('counter', [])),
        ];
    }
}
