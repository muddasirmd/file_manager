<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\FilesActionRequest;
use App\Http\Requests\ShareFilesRequest;
use App\Http\Requests\TrashFilesRequest;
use App\Http\Requests\AddToFavouritesRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\File;
use App\Models\FileShare;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FileResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\StarredFile;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShareFilesMail;

class FileController extends Controller
{
    public function myFiles(Request $request ,string $folder = null)
    {
        if($folder){
            $folder = File::query()->where('created_by', Auth::id())
                ->where('path', $folder)
                ->firstOrFail();
        }
        
        if(!$folder){
            $folder = $this->getRoot();
        }

        $favourites = (int)$request->get('favourites');


        $query = File::query()
                ->select('files.*')
                ->where('parent_id', $folder->id)
                ->where('created_by', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('files.created_at', 'desc')
                ->orderBy('files.id', 'desc');
        if($favourites){
            // $query->whereIn('id', function($query){
            //     $query->select('file_id')
            //         ->from('starred_files')
            //         ->where('user_id', Auth::id());
            // });
            $query->join('starred_files', 'starred_files.file_id', 'files.id')
            ->where('starred_files.user_id', Auth::id());
        }
        
        $files = $query->paginate(10);


        $files = FileResource::collection($files);

        // If the request wants JSON, return the files directly
        // This is useful for API responses or AJAX requests (Pagination)
        if($request->wantsJson()){
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]); // include the folder itself in the ancestors

        $folder = new FileResource($folder);
        
        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
    }

    public function trash(Request $request){
        
        $files = File::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        // If the request wants JSON, return the files directly
        // This is useful for API responses or AJAX requests (Pagination)
        if($request->wantsJson()){
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('Trash', compact('files'));
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

    public function destroy(FilesActionRequest $request){

        $data = $request->validated();
        $parent = $request->parent;

        if($data['all']){

            $children = $parent->children;

            foreach($children as $child){
                // $child->delete(); // will update deleted_at of children
                $child->moveToTrash();
            }
        }
        else{
            foreach($data['ids'] ?? [] as $id){
                $file = File::find($id);
                // $file->delete(); // will also delete children files of folder
                $file->moveToTrash();
            }
        }

        return to_route('myFiles', ['folder' => $parent ? $parent->path : '/']);
    }

    public function download(FilesActionRequest $request){
        
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if(!$all && empty($ids)){
            return [
                'message' => 'Please select files to download'
            ];
        }

        if($all){
            $url = $this->createZip($parent->children);
            $fileName = $parent->name . '.zip';
        }
        else{
            [$url, $fileName] = $this->getDownloadUrl($ids, $parent->name);
        }

        return [
            'url' => $url,
            'filename' => $fileName
        ];
    }

    public function createZip($files): string
    {
        $zipPath = 'zip/'. Str::random(). '.zip';
        $publicPath = Storage::disk('public')->path('');

        if (!Storage::disk('public')->exists('zip')) {
            Storage::disk('public')->makeDirectory('zip');
        }

        $zipFile =  $publicPath . $zipPath;

        $zip = new \ZipArchive();

        if($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true){
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = ''){
        
        foreach($files as $file){
            if($file->is_folder){
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            }
            else{
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
            }
        }
    }

    public function restore(TrashFilesRequest $request){
        
        $data = $request->validated();
        if($data['all']){
            $records = File::onlyTrashed()->get();
            foreach($records as $rec){
                $rec->restore();
            }
        }
        else{
            $ids = $data['ids'] ?? [];
            $records = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach($records as $rec){
                $rec->restore();
            }
        }

        return to_route('trash');
    }    
    

    public function deleteForever(TrashFilesRequest $request){

        $data = $request->validated();
        if($data['all']){
            $records = File::onlyTrashed()->get();
            foreach($records as $rec){
                $rec->deleteForever();
            }
        }
        else{
            $ids = $data['ids'] ?? [];
            $records = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach($records as $rec){
                $rec->deleteForever();
            }
        }

        return to_route('trash');
    }


    public function addToFavourites(AddToFavouritesRequest $request){
        
        $data = $request->validated();
    
        $id = $data['id'];
        $file = File::find($id);
        $user_id = Auth::id();

        $starredFile = StarredFile::query()
            ->where('file_id', $file->id)
            ->where('user_id', $user_id)
            ->first();
        
        if($starredFile){
            $starredFile->delete();
        }
        else{
            StarredFile::create([
                'file_id' => $file->id,
                'user_id' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
      
        // Batch Insert
        // StarredFile::insert($data);

        return redirect()->back()->with('success', 'Favourites updated!');
    }


    public function share(ShareFilesRequest $request){

        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];
        $email = $data['email'];

        if(!$all && empty($ids)){
            return [
                'message' => 'Please select files to share'
            ];
        }

        $user = User::query()->where('email', $email)->first();

        if(!$user){
            return redirect()->back()->with('error', 'User with this email does not exist');
        }

        if($all){
            $files = $parent->children;
        }
        else{
            $files = File::find($ids);
        }

        $data = [];
        $ids = Arr::pluck($files, 'id');
        $existingFileIds = FileShare::query()
            ->where('user_id', $user->id)
            ->whereIn('file_id', $ids)
            ->get()
            ->keyBy('file_id');

        foreach($files as $file){
            if($existingFileIds->has($file->id)){
                continue;
            }
            
            $data[] = [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        FileShare::insert($data);

        Mail::to($user)->send(new ShareFilesMail($user, Auth::user(), $files));

        return redirect()->back();
    }

    public function sharedWithMe(Request $request){
        
        $files = File::getSharedWithMe()
            ->paginate(10);

        // If the request wants JSON, return the files directly
        // This is useful for API responses or AJAX requests (Pagination)
        if($request->wantsJson()){
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('SharedWithMe', compact('files'));
    }


    public function sharedByMe(Request $request){
        
        $files = File::getSharedByMe()
            ->paginate(10);

        // If the request wants JSON, return the files directly
        // This is useful for API responses or AJAX requests (Pagination)
        if($request->wantsJson()){
            return $files;
        }

        $files = FileResource::collection($files);

        return Inertia::render('SharedByMe', compact('files'));
    }

    
    public function downloadSharedWithMe(FilesActionRequest $request){
        
        $data = $request->validated();

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if(!$all && empty($ids)){
            return [
                'message' => 'Please select files to download'
            ];
        }

        $zipName = 'shared_with_me';

        if($all){
            $files = File::getSharedWithMe()->get();
            $url = $this->createZip($files);
            $fileName = $zipName.".zip";
        }
        else{
            // dd ($this->getDownloadUrl($ids, $zipName));
            [$url, $fileName] = $this->getDownloadUrl($ids, $zipName);
        }

        return [
            'url' => $url,
            'filename' => $fileName
        ];
    }    
    
    
    public function downloadSharedByMe(FilesActionRequest $request){
        
        $data = $request->validated();

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if(!$all && empty($ids)){
            return [
                'message' => 'Please select files to download'
            ];
        }

        $zipName = 'shared_by_me';

        if($all){
            $files = File::getSharedByMe()->get();
            $url = $this->createZip($files);
            $fileName = $zipName.".zip";
        }
        else{
            // dd ($this->getDownloadUrl($ids, $zipName));
            [$url, $fileName] = $this->getDownloadUrl($ids, $zipName);
        }

        return [
            'url' => $url,
            'filename' => $fileName
        ];
    }

    private function getDownloadUrl(array $ids, $zipName){

        
            if(count($ids) == 1){
                $file = File::find($ids[0]);
                if($file->is_folder){
                    if($file->children->count() == 0){
                        return [
                            'message' => 'The folder is empty'
                        ];
                    }
                    
                    $url = $this->createZip($file->children);
                    $fileName = $file->name . '.zip';
                }
                else{
                    $dest = 'public/'.pathinfo($file->storage_path, PATHINFO_BASENAME);
                    
                    // if files in storage folder are saved in app/
                    // Storage::copy($file->storage_path, $dest);

                    $filename = pathinfo($file->storage_path, PATHINFO_BASENAME);

                    // if files in storage folder are saved in app/private
                    // Copy from private to public
                    Storage::disk('public')->put(
                        $filename,
                        Storage::disk('local')->get($file->storage_path)
                    );
                
                    $url = asset(Storage::url($dest));
                    $fileName = $file->name;
                }
            }
            else{
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);

                $fileName = $zipName . '.zip';
            }

            return [ $url, $fileName];
    }
    
}
