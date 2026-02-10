<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $existingFile = AudioFile::where('user_id', Auth::id())
            ->where('filename', $file->getClientOriginalName())
            ->where('size', $file->getSize())
            ->first();

        if ($existingFile) {
            return response()->json([
                'success' => false,
                'message' => 'File "' . $file->getClientOriginalName() . '" has already been uploaded.',
            ], 409);
        }

        $filePath = $file->store('audio_files', 'public');

        $audioFile = AudioFile::create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $filePath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'uploaded',
            'uploaded_at' => now(),
        ]);

        // TODO: Publish event to queue for processing
        // dispatch(new ProcessAudioFileJob($audioFile));

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_info' => [
                'id' => $audioFile->id,
                'name' => $audioFile->filename,
                'size' => $audioFile->size,
                'extension' => pathinfo($audioFile->filename, PATHINFO_EXTENSION),
            ]
        ]);
    }
}
