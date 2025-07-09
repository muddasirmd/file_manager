<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Support\Facades\Auth;

class StoreFileRequest extends ParentIdBaseRequest
{

    protected function prepareForValidation()
    {
        // For folders and nested folders
        $paths = array_filter($this->relative_paths ?? [], fn($f) => $f != null);

        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectMainFolderName($paths)
        ]);
    }

    protected function passedValidation(){

        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths, $data['files'])
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /**
         * Using array_merge to include all post data. without this parent_id passed in the post request params will not be available in the controller or elsewhere.
         */
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    // If files are in a folder then no need to run the validation
                    if(!$this->folder_name){
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::query()->where('name', $value->getClientOriginalName())
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at') // Ensure the file is not deleted
                            ->exists();

                        if ($file) {
                            $fail('File "' . $value->getClientOriginalName() . '" already exists.');
                        }
                    }
                }
            ],
            'folder_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail): void {
                    if($value){
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::query()->where('name', $value)
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->where('is_folder', 1) // Ensure it's a folder
                            ->whereNull('deleted_at') // Ensure the file is not deleted
                            ->exists();

                        if ($file) {
                            $fail('Folder "' . $value . '" already exists.');
                        }
                    }
                }
            ]
        ]);
    }


    public function detectMainFolderName($paths){
        
        /**
         * $paths = [cars/bmw/1.jpg, cars/bmw/5.jpg, cars/audi/12.jpg]
         */
        if(!$paths){
            return null;
        }
        // Extract the folder name from the first path
        $parts = explode('/', $paths[0]); // cars/bmw/1.jpg
        return $parts[0]; // cars;
    }

    public function buildFileTree($filePaths, $files){

        // Keeping file paths limited to the number of files uploaded. This is to ensure that the file paths match the files being uploaded
        // There is a certain limit to how many files can be uploaded at once (can be changed in configuration of php or docker), so we limit the file paths to that number
        $filePaths = array_slice($filePaths, 0, count($files));
        $filePaths = array_filter($filePaths, fn($f) => $f != null);

        $tree = [];

        /*
         * [
         *   ecommerce => [
         *      products => [
         *             1.jpg => UploadedFile Object
         *      ]
         *    ],
         * ] 
         */

         foreach ($filePaths as $ind => $filePath){
            $parts = explode('/', $filePath); // ecommerce, products, 1.jpg
            
            // Reference to the current node in the tree. This will allow us to build the tree structure dynamically.
            // We will use a reference to the current node so that we can modify it directly
            $currentNode = &$tree;

            // Iterate through the parts of the file path and build the tree structure
            foreach($parts as $i => $part){
                // If the part is not already in the current node, we will create an empty array for it
                if(!isset($currentNode[$part])){
                    $currentNode[$part] = [];
                }


                // If this is the last part of the path, we will assign the file to this part. Otherwise, we will move the reference to the next part.
                if($i === count($parts) - 1){
                    $currentNode[$part] = $files[$ind];
                }
                else{
                    $currentNode = &$currentNode[$part];
                }
            }
        }

        return $tree;
    }
}
