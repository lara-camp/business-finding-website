<?php

namespace App\Http\Controllers\Backend;

use App\Models\Blog;
use Inertia\Inertia;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Http\Resources\BlogCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::paginate(10);
        return Inertia::render('Backend/Blog/Index', ['blogs' => new BlogCollection($blogs),]);
    }

    public function create()
    {
        return Inertia::render('Backend/Blog/Create',[
            'blog' => new Blog(),
        ]);
    }
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'tag' => 'required|string',
            'cover_image' => 'required',
            // 'image_attachment' => 'required',
            'content' => 'required',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->route('admin.blog.create')
                ->withErrors($validator)
                ->withInput();
        }

        $blog = Blog::create([
            'title' => $request->title,
            'body' => $request->description,
            'tag' => $request->tag,
            'content' => $request->content,
            'user_id' => auth()->user()->id,
            'status' => 1,
        ]);
        // dd($request->hasFile('cover_image'));
        if($request->hasFile('cover_image'))
        {
            $file = 'cover'.time().auth()->id().'-'.$_FILES['cover_image']['name'];
            // dd($file);k
            $path = Storage::disk('public')->put( $file,fopen($request->file('cover_image'), 'r+'));
            $blog->cover_image = $file ;
            $blog->save();

        }
        if($request->image_attachments)
        {
            foreach($request->image_attachments as $image)
            {
                $file = 'img_'.time().auth()->id().'-'.$image->getClientOriginalName();
                // dd($file);
                $path = Storage::disk('public')->put( $file,fopen($image, 'r+'));
                $blog->cover_image = $file ;
                Image::create([
                    'url' => $file ,
                    'imageable_id' => $blog->id,
                    'imageable_type' => 'App\Models\Blog',
                ]);
            }
        }

        // Optionally, you can redirect back or return a response
        return redirect()->route('admin.blog'); // Redirect to the index page or any other page as needed
    }

    public function edit($id, Request $request)
    {
        // dd($request->all());
        $blog = Blog::findOrFail($id);
        return Inertia::render('Backend/Blog/Edit', ['blog' => new BlogResource($blog),]);
    }

    public function update($id, Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'tag' => 'required|string',
            'content' => 'required',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->route('admin.blog.edit')
                ->withErrors($validator)
                ->withInput();
        }

        $blog = Blog::findOrFail($id);
        if($request->cover_image != null && $request->hasFile('cover_image'))
        {
            // $image = Image::where('imageable_id',$blog->id)->where('imageable_type', 'App\Models\Blog')->first();
            // dd( $image->url);
            if(Storage::disk('public')->exists($blog->cover_image))
            {
                Storage::disk('public')->delete($blog->cover_image);
            }

        }
        $blog->update($request->all());
        return to_route('admin.blog');
    }

    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        return Inertia::render('Backend/Blog/Show', ['blog' => new BlogResource($blog),]);
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        $images = Image::where('imageable_id',$blog->id)->where('imageable_type', 'App\Models\Blog')->get();
        foreach($images as $image)
        {
            if(Storage::disk('public')->exists($image->url))
            {
                Storage::disk('public')->delete($image->url);
            }
            $image->delete();
        }

        $blog->delete();
        return to_route('admin.blog');
    }
}
