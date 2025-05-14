<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ParentIdBaseRequest;
use App\Models\File; // Ensure this is the correct namespace for the File model
use Illuminate\Validation\Rule; // Import the Rule class for validation
use Illuminate\Support\Facades\Auth; // Import the Auth facade for authentication

class StoreFolderRequest extends ParentIdBaseRequest
{
 

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(),
            [
                'name' => [
                    'required',
                    Rule::unique(File::class, 'name')
                    ->where('created_by', Auth::id())
                    ->where('parent_id', $this->parent_id)
                    ->whereNull('deleted_at')
                ],
            ]
        );
    }

    public function messages()
    {
        return [
            'name.unique' => 'Folder ":input" already exists',
        ];
    }
}
