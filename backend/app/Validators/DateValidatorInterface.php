<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

interface DateValidatorInterface
{
    public function isValid($datetime): bool;
}
