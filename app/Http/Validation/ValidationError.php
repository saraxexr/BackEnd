<?php

namespace App\Http\Validation;

use Illuminate\Support\Facades\Validator;

class ValidationError{

    public static function validationRequest($request, $rules){
        $validator= Validator::make($request, $rules);
        return $validator;
    }

    public static function validationUserInput($request, $rules){
        $validator= Validator::make($request->all(), $rules);
        return $validator;
    }

    public static function sanitizeArray($address){
        $sanitizedAddress = filter_var_array($address, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        return $sanitizedAddress;
    }
}

