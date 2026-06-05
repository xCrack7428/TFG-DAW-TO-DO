<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index()
    {
        // Cogemos todas las reseñas y el nombre de su autor, de más nueva a más antigua
        $reviews = Review::with('user')->latest()->get();

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->reviews()->create($validated);

        return redirect()->back();
    }
}