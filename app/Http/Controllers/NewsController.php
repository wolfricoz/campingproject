<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class NewsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('News/Index', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'news' => News::query()->published()->latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function admin(): Response
    {
        return Inertia::render('Admin/News/Index', [
            'news' => News::query()->where('status', 1)->latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! auth()->user()->hasPermissionTo('manage news')) {
            abort(403);
        }
        $data = $request->validate([
            'title' => 'required|max:100',
            'summary' => 'nullable|max:2048',
            'content' => 'required|max:10480',
            'type' => 'required|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'published' => 'required|boolean',
        ]);

        $data['slug'] = str_replace(' ', '-', $data['title']);

        if ($request->hasFile('image')) {
            $manager = new ImageManager(new Driver);

            $encoded = $manager->read($request->file('image'))
                ->cover(400, 400)
                ->toWebp(80);

            $path = 'images/'.Str::uuid().'.webp';
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
