<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class DateValidator implements DateValidatorInterface
{
    public function isValid($datetime): bool
    {
        $validator = Validator::make(['datetime' => $datetime], [
            'datetime' => 'required|date_format:Y-m-d H:i:s',
        ]);

        return !$validator->fails();
    }
}
