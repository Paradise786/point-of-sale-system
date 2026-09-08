<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1>Edit Post</h1>
 <form method="POSt" action="{{ route('posts.update',$post->id)}}">
    @csrf
    @method('PUT') <!-- Tells Laravel to treat this as a put request -->
    <label>Title:</label>
    <input type="text" name="title" value="{{ old('title',4post->title) }}">
    <label>Body:</label>
    <textarea name="body">{{ old('body',$post->) }} </textarea>
    <button type="submit">Update</button>

</form>
</body>
</html>