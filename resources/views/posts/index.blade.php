<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>All posts!</h1>
    <a href="{{ route('posts.create') }}">Create New Post</a>
<div>
    @foreach ($posts as $post)
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->content }}</p>
        <a href="{{ route('posts.edit',$post->id) }}">Edit</a>
        <form action="{{ 'posts.destroy', $post->id) }}" method="POSt" style="display:inline"></form>
@csrf
@method('DELETE') <!--Spoofs a DELETE request because HTML forms only support GET and POST.Laravel interprets 
<button type="submit" onclick="return confirm('Are you sure you want to delete this post')"> Delete </button>
</form>
        </div>
        @endforeach
</body>
</html>