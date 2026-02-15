<?php

namespace App\Http\Controllers;

use App\Enums\AudioFileStatus;
use App\Models\AudioFile;
use App\Pipelines\ProcessAudioPipeline;
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
            'status' => AudioFileStatus::UPLOADED->value,
            'uploaded_at' => now(),
        ]);

        (new ProcessAudioPipeline())->handle($audioFile->id);

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

    /**
     * Get user's uploaded files.
     */
    public function getFiles(): JsonResponse
    {
        $audioFiles = AudioFile::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'size' => $file->size,
                    'size_formatted' => $file->size_formatted,
                    'uploaded_at' => $file->uploaded_at->diffForHumans(),
                    'status' => $file->status,
                    'transcription_status' => $file->transcription ? $file->transcription->status : null,
                ];
            });

        return response()->json([
            'success' => true,
            'files' => $audioFiles,
        ]);
    }

    /**
     * Delete an uploaded file.
     */
    public function delete(string $id): JsonResponse
    {
        $audioFile = AudioFile::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$audioFile) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        \Storage::delete($audioFile->path);

        $audioFile->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
        ]);
    }
}
