<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} - Audio Transcriber</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/upload.css'])
</head>
<body class="font-sans antialiased min-h-screen bg-gray-50 flex items-center justify-center p-6">
<div class="w-full max-w-[800px] lg:w-[900px] lg:max-w-4xl">
    <div class="bg-white rounded-t-lg lg:rounded-t-none lg:rounded-tl-lg lg:rounded-r-lg shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] border border-[#e3e3e0] overflow-hidden min-h-[80vh]">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button
                    class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-[#706f6c] hover:text-[#1b1b18] hover:border-gray-300 cursor-pointer active-tab"
                    data-tab="upload"
                >
                    Upload
                </button>
                <button
                    class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-[#706f6c] hover:text-[#1b1b18] hover:border-gray-300 cursor-pointer"
                    data-tab="files"
                >
                    Files
                </button>
                <button
                    class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-[#706f6c] hover:text-[#1b1b18] hover:border-gray-300 cursor-pointer"
                    data-tab="transcriptions"
                >
                    Transcriptions
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6 lg:p-8 h-[calc(80vh-100px)] overflow-y-auto">
            <!-- Upload Tab Content -->
            <div id="upload-tab" class="tab-content active">
                <h1 class="text-[#1b1b18] text-lg font-medium mb-6">AI Audio Transcriber</h1>

                <div class="mb-6">
                    <label for="audio_file" class="block text-sm font-medium text-[#1b1b18] mb-2">
                        Upload Audio File
                    </label>
                    <div class="flex items-center justify-center w-full drop-zone">
                        <label for="audio_file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-4 text-[#706f6c]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="mb-2 text-sm text-[#706f6c]">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-[#706f6c]">
                                    MP3, WAV, FLAC, M4A (MAX. 100MB)
                                </p>
                            </div>
                            <input id="audio_file" type="file" class="hidden" accept="audio/*" />
                        </label>
                    </div>
                    <p id="file-info" class="mt-2 text-sm text-[#706f6c] hidden"></p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <button id="upload-btn" class="relative overflow-hidden w-full px-5 py-2.5 text-sm font-medium rounded-lg border border-black text-white bg-black cursor-pointer transition-transform duration-200 active:scale-[0.97] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 before:absolute before:inset-0 before:bg-gradient-to-r before:from-gray-800 before:to-black before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100">
                        <span class="relative z-10">Transcribe Audio</span>
                    </button>
                </div>

                <div id="result" class="mt-6 hidden">
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <p class="text-red-700 text-sm font-medium">Status: <span id="status-text"></span></p>
                        <p id="result-message" class="mt-2 text-gray-700 text-sm"></p>
                    </div>
                </div>
            </div>

            <!-- Files Tab Content -->
            <div id="files-tab" class="tab-content hidden">
                <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>

                <div class="space-y-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">sample_audio.mp3</h3>
                                <p class="text-sm text-[#706f6c]">Size: 15.2 MB | Uploaded: Today</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">meeting_recording.wav</h3>
                                <p class="text-sm text-[#706f6c]">Size: 28.7 MB | Uploaded: Yesterday</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">interview.flac</h3>
                                <p class="text-sm text-[#706f6c]">Size: 42.1 MB | Uploaded: Feb 7, 2026</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">presentation.mp3</h3>
                                <p class="text-sm text-[#706f6c]">Size: 35.4 MB | Uploaded: Feb 6, 2026</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">podcast_episode.wav</h3>
                                <p class="text-sm text-[#706f6c]">Size: 52.8 MB | Uploaded: Feb 5, 2026</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-[#1b1b18]">voice_note.m4a</h3>
                                <p class="text-sm text-[#706f6c]">Size: 8.3 MB | Uploaded: Feb 4, 2026</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transcriptions Tab Content -->
            <div id="transcriptions-tab" class="tab-content hidden">
                <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Transcriptions</h2>

                <div class="space-y-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of sample_audio.mp3</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Completed | Created: Today</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of meeting_recording.wav</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Processing | Created: Yesterday</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of interview.flac</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Failed | Created: Feb 7, 2026</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200">Retry</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of presentation.mp3</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Completed | Created: Feb 6, 2026</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of podcast_episode.wav</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Completed | Created: Feb 5, 2026</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-[#1b1b18]">Transcription of voice_note.m4a</h3>
                                <p class="text-sm text-[#706f6c] mt-1">Status: Completed | Created: Feb 4, 2026</p>
                                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                                    Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?
                                </p>
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/upload.js'])
</body>
</html>
