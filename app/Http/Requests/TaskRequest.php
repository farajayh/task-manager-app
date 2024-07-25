<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\JsonResponse;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Enums\TaskStatus;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ($this->isMethod('POST') ? $this->store() : $this->update());
    }


    public function store(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'due_date'    => ['required', 'date', 'date_format:Y-m-d']
        ];
    }

    public function update(): array
    {
        return [
            'title'       => ['string', 'max:255'],
            'description' => ['string', 'min:10'],
            'due_date'    => ['date', 'date_format:Y-m-d'],
            'status'      => ['string', Rule::enum(TaskStatus::class)]
        ];
    }

    

    public function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => "Request Failed",
                'errors' => $errors
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => "Request Aborted",
                'errors' => "Not Authorized"
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
