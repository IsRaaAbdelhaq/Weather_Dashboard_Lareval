<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Favorite::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city_name' => ['required', 'string', 'max:100', 'not_regex:/[0-9]/'],
            'country_code' => 'nullable|string|max:2',
            'notes' => 'nullable|string|max:255',
        ]);

        $favorite = Favorite::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Added to favorites',
            'data' => $favorite
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $favorite = Favorite::findOrFail($id);

        $favorite->update([
            'notes' => $request->notes
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully',
            'data' => $favorite
        ]);
    }

    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully'
        ]);
    }
}