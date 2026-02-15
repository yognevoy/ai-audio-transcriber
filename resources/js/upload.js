let loadedFiles = [];

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('audio_file');
    const fileInfo = document.getElementById('file-info');

    const dropZone = document.querySelector('.drop-zone');

    const uploadBtn = document.getElementById('upload-btn');

    const resultDiv = document.getElementById('result');
    const statusText = document.getElementById('status-text');
    const resultMessage = document.getElementById('result-message');

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();

        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            fileInput.files = e.dataTransfer.files;
            fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            fileInfo.classList.remove('hidden');
        }
    });

    fileInput.addEventListener('change', function (e) {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileInfo.textContent = `Selected: ${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
            fileInfo.classList.remove('hidden');
        } else {
            fileInfo.classList.add('hidden');
        }
    });

    function setLoading(isLoading) {
        uploadBtn.disabled = isLoading;
        uploadBtn.classList.toggle('opacity-70', isLoading);
        uploadBtn.classList.toggle('cursor-not-allowed', isLoading);

        uploadBtn.querySelector('.btn-text')
            .classList.toggle('hidden', isLoading);

        uploadBtn.querySelector('.btn-loader')
            .classList.toggle('hidden', !isLoading);
    }

    function showSuccess(messageHtml) {
        statusText.textContent = 'Success';
        document.getElementById('result-container').className = 'bg-green-50 border border-green-200 rounded-md p-4';
        document.getElementById('status-text-element').className = 'text-green-700 text-sm font-medium';
        resultMessage.innerHTML = messageHtml;
    }

    function showError(message) {
        statusText.textContent = 'Error';
        document.getElementById('result-container').className = 'bg-red-50 border border-red-200 rounded-md p-4';
        document.getElementById('status-text-element').className = 'text-red-700 text-sm font-medium';
        resultMessage.textContent = message;
    }

    async function uploadFile(file) {
        const formData = new FormData();
        formData.append('audio_file', file);

        const response = await fetch('/api/upload', {
            method: 'POST',
            body: formData,
        });

        return response.json();
    }

    uploadBtn.addEventListener('click', async function () {
        const file = fileInput.files[0];

        if (!file) {
            alert('Please select an audio file to upload.');
            return;
        }

        setLoading(true);

        try {
            const data = await uploadFile(file);

            resultDiv.classList.remove('hidden');

            if (data.success) {
                const messageHtml = `
                        <strong>File uploaded successfully!</strong><br>
                        Name: ${data.file_info.name}<br>
                        Size: ${data.file_info.size} bytes<br>
                        Extension: ${data.file_info.extension}
                    `;
                showSuccess(messageHtml);

                await loadFiles();
            } else {
                showError(data.message || 'An error occurred during upload.');
            }
        } catch (error) {
            resultDiv.classList.remove('hidden');
            showError('Network error occurred. Please try again.');
            console.error('Upload error:', error);
        } finally {
            setLoading(false);
        }
    });

    document.querySelectorAll('[data-tab="files"]').forEach(button => {
        button.addEventListener('click', async () => {
            await loadFiles();
        });
    });
});

async function loadFiles() {
    try {
        const filesTab = document.getElementById('files-tab');
        filesTab.innerHTML = `
            <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
            <div class="flex justify-center items-center py-8">
                <svg class="animate-spin h-8 w-8 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;

        const response = await fetch('/api/files');
        const data = await response.json();

        if (data.success) {
            loadedFiles = data.files;
            renderFilesList(data.files);
        } else {
            showErrorInFilesTab('Failed to load files');
        }
    } catch (error) {
        console.error('Error loading files:', error);
        showErrorInFilesTab('Error loading files');
    }
}

function renderFilesList(files) {
    const filesTab = document.getElementById('files-tab');

    if (files.length === 0) {
        filesTab.innerHTML = `
            <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
            <div class="text-center py-12">
                <p class="text-gray-500">No files uploaded yet</p>
            </div>
        `;
        return;
    }

    const filesHtml = files.map(file => `
        <div class="p-4 border border-gray-200 rounded-lg" data-file-id="${file.id}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-medium text-[#1b1b18]">${file.filename}</h3>
                    <p class="text-sm text-[#706f6c]">Size: ${file.size_formatted} | Uploaded: ${file.uploaded_at}</p>
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 cursor-pointer delete-btn" data-file-id="${file.id}">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    filesTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
        <div class="space-y-4">
            ${filesHtml}
        </div>
    `;

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const fileId = this.getAttribute('data-file-id');
            await deleteFile(fileId);
        });
    });
}

async function deleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }

    try {
        const fileElement = document.querySelector(`[data-file-id="${fileId}"]`);
        if (fileElement) {
            fileElement.innerHTML = `
                <div class="flex justify-center items-center py-4">
                    <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            `;
            fileElement.classList.add('opacity-50');
        }

        const response = await fetch(`/api/files/${fileId}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadedFiles = loadedFiles.filter(file => file.id !== fileId);

            renderFilesList(loadedFiles);
        } else {
            alert(data.message || 'Failed to delete file');

            await loadFiles();
        }
    } catch (error) {
        console.error('Error deleting file:', error);
        alert('Error deleting file');

        await loadFiles();
    }
}

function showErrorInFilesTab(message) {
    const filesTab = document.getElementById('files-tab');
    filesTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-red-700 text-sm">${message}</p>
        </div>
    `;
}

function initTabs() {
    const buttons = document.querySelectorAll('.tab-button');
    const tabs = document.querySelectorAll('.tab-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.dataset.tab;

            buttons.forEach(btn => btn.classList.remove('active-tab'));
            button.classList.add('active-tab');

            tabs.forEach(tab => tab.classList.add('hidden'));

            const activeTab = document.getElementById(`${tabName}-tab`);
            activeTab.classList.remove('hidden');

            if (tabName === 'files') {
                loadFiles();
            }
        });
    });
}

initTabs();
