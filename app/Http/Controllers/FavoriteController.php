<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::all();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $favorites
            ]);
        }

        return view('weather.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_name' => 'required|string|max:100|regex:/^[a-zA-Z\s]+$/',
            'country_code' => 'nullable|regex:/^[A-Z]{2}$/',
            'notes' => 'nullable|string|max:255',
        ]);

        $favorite = Favorite::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $favorite
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:255'
        ]);

        $favorite = Favorite::findOrFail($id);
        $favorite->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $favorite
        ]);
    }

    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }
}