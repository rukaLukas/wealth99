<?php
namespace App\Http\Requests;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;

class DateTimeRequest extends FormRequest
{
    public function rules()
    {       
        return [
            'datetimepath' => 'required|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages()
    {
        return [
            'datetimepath.date_format' => 'The datetime must be in the format: Y-m-d H:i:s',
        ];
    }

    public function authorize()
    {
        return true;
    }  
    
    protected function validationData()
    {
        return $this->route()->parameters();
    } 
}
