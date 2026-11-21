<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'jobs' => JobListing::query()
                ->open()
                ->with(['postedBy', 'category', 'skills'])
                ->latest('published_at')
                ->take(3)
                ->get(),
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('name')
                ->get(),
            'stats' => [
                'jobs' => JobListing::query()->open()->count(),
                'companies' => User::query()->where('role', 'employer')->count(),
                'categories' => Category::query()->count(),
            ],
        ]);
    }
}
