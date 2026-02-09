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
</head>
<body class="font-sans antialiased min-h-screen bg-gray-50 flex items-center justify-center p-6">
<div class="w-full max-w-[335px] lg:w-[438px] lg:max-w-4xl">
    <div class="bg-white rounded-t-lg lg:rounded-t-none lg:rounded-tl-lg lg:rounded-r-lg shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] border border-[#e3e3e0] overflow-hidden">
        <div class="p-6 lg:p-8">
            <h1 class="text-[#1b1b18] text-lg font-medium mb-6">AI Audio Transcriber</h1>

            <div class="mb-6">
                <label for="audio_file" class="block text-sm font-medium text-[#1b1b18] mb-2">
                    Upload Audio File
                </label>
                <div class="flex items-center justify-center w-full">
                    <label for="audio_file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
                <button class="relative overflow-hidden w-full px-5 py-2.5 text-sm font-medium rounded-lg border border-black text-white bg-black cursor-pointer transition-transform duration-200 active:scale-[0.97] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 before:absolute before:inset-0 before:bg-gradient-to-r before:from-gray-800 before:to-black before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100">
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('audio_file');
        const fileInfo = document.getElementById('file-info');
        const uploadBtn = document.getElementById('upload-btn');
        const resultDiv = document.getElementById('result');
        const statusText = document.getElementById('status-text');
        const resultMessage = document.getElementById('result-message');

        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileInfo.textContent = `Selected: ${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
                fileInfo.classList.remove('hidden');
            } else {
                fileInfo.classList.add('hidden');
            }
        });

        uploadBtn.addEventListener('click', async function() {
            const file = fileInput.files[0];

            if (!file) {
                alert('Please select an audio file to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('audio_file', file);

            statusText.textContent = 'Uploading...';
            resultMessage.textContent = 'Processing your audio file...';
            resultDiv.classList.remove('hidden');

            try {
                const response = await fetch('/api/upload', {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    statusText.textContent = 'Success';
                    resultMessage.innerHTML = `
                            <strong>File uploaded successfully!</strong><br>
                            Name: ${data.file_info.name}<br>
                            Size: ${data.file_info.size} bytes<br>
                            Extension: ${data.file_info.extension}
                        `;
                } else {
                    statusText.textContent = 'Error';
                    resultMessage.textContent = data.message || 'An error occurred during upload.';
                }
            } catch (error) {
                statusText.textContent = 'Error';
                resultMessage.textContent = 'Network error occurred. Please try again.';
                console.error('Upload error:', error);
            }
        });
    });
</script>
</body>
</html>
