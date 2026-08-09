<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Str;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::query()->where('status', 1)->where('published', true)->latest()->paginate(10)
        ->withQueryString();

        foreach ($news as $newsArticle) {
            if ($newsArticle->image === "images/header.jpg") {
                continue;
            }

            $newsArticle->image = Storage::disk('public')->url($newsArticle->image);
        }

        return Inertia::render('News/Index', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'news' => $news
        ]);

    }

    public function admin()
    {
        return Inertia::render('Admin/News/Index', [
            'news' => News::query()->where('status', 1)->latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        if (! auth()->user()->hasPermissionTo('manage news')) {
            abort(403);
        }
        // validate the data
        $data = $request->validate([
            'title' => 'required|max:100',
            'summary' => 'nullable|max:2048',
            'content' => 'required|max:10480',
            'type' => 'required|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'published' => 'required|boolean',
        ]);

        // Fill the slug
        $data['slug'] = str_replace(' ', '-', $data['title']);

        if ($request->hasFile('image')) {
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            // if image is present, we clean it before storing it and keep the returned path
            $encoded = $manager->read($request->file('image'))
                ->cover(400, 400)
                ->toWebp(80);

            $path = 'images/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        $news = News::create($data);


        return response()->json([
            'success' => true,
            'data' => $news,
        ]);

    }
}
