<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UploadController extends Controller
{
    /**
     * Show the upload page.
     */
    public function index(): View
    {
        return view('upload');
    }

    /**
     * Handle file upload API request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,flac,m4a|max:102400',
        ]);

        $file = $request->file('audio_file');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ]
        ]);
    }
}
