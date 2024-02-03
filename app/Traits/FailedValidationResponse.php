<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;

trait FailedValidationResponse
{
    use ApiResponse;
    public function failedValidationResponse($validator)
    {
        throw new HttpResponseException(
            $this->apiResponse(
                data: $validator->errors(),
                message: "error in body request",
                statuscode: 404,
                error: true
            )
        );
    }
}
