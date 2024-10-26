<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DateTimeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'datetime' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages()
    {
        return [
            'datetime.required' => 'The datetime field is required.',
            'datetime.date_format' => 'The datetime must be in the format: Y-m-d H:i:s',
        ];
    }

    protected function validationData()
    {
        return array_merge($this->all(), [
            'datetime' => $this->route('datetime'),
        ]);
    }
}
