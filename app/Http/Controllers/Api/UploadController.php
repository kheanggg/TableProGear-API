<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // only images up to 2MB
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
