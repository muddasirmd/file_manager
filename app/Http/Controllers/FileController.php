<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FileResource;

class FileController extends Controller
{
    public function myFiles(?string $folder = null)
    {
        if($folder){
            $folder = File::query()->where('created_by', Auth::id())
                ->where('path', $folder)
                ->firstOrFail();
        }
        if(!$folder){
            $folder = $this->getRoot();
        }

        $files = File::query()
                ->where('parent_id', $folder->id)
                ->where('created_by', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);


        $files = FileResource::collection($files);

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]); // include the folder itself in the ancestors

        $folder = new FileResource($folder);
        
        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
    }

    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if(!$parent){
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }


    public function store(StoreFileRequest $request){

        $data = $request->validated();
        $parent = $request->parent;
        $user = $request->user();
        $fileTree = $request->file_tree;
        
        if(!$parent){
            $parent = $this->getRoot();
        }

        // Folder Upload
        if(!empty($fileTree)){
            $this->saveFileTree($fileTree, $parent, $user);
        }
        else{
            // File Upload 
            foreach($data['files'] as $file){
                /**
                 * @var \Illuminate\Http\UploadedFile $file
                 */
                $this->saveFile($file, $user, $parent);
            }
        }
    }

    private function getRoot()
    {
      return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    public function saveFileTree($fileTree, $parent, $user){
        
        foreach($fileTree as $name => $file){
            // if the file is a folder, 
            if(is_array($file)){
                $folder = new File();
                $folder->is_folder = 1;
                $folder->name = $name;

                // appendNode is a method to add the folder to the parent node
                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            }
            else{

                $this->saveFile($file, $user, $parent);
            }
        }
        
    }

    public function saveFile($file, $user, $parent){
        /**
         * @var \Illuminate\Http\UploadedFile $file
         */
        $path = $file->store('/files/'.$user->id);

        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        
        $parent->appendNode($model);
    }
}
