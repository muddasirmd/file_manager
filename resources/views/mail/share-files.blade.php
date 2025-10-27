<p>Hello <b>{{ $user->name }}</b>,</p>

<p>User <b>{{ $author->name }}</b> has shared following files with you.</p>

<hr>
@foreach ($files as $file)
    <p> {{ $file->is_folder ? 'Folder' : 'File' }} - {{ $file->name }}</p>
@endforeach