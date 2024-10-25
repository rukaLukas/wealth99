<?php
namespace App\Http\Requests;

use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Http\FormRequest;

class DateTimeRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge(['datahora' => $this->datahora]); 
    }

    public function rules()
    {       
        return [
            'datahora' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages()
    {
        return [
            'datahora.date_format' => 'The datetime must be in the format: Y-m-d H:i:s',
        ];
    }

    public function authorize()
    {
        return true;
    }    
}
