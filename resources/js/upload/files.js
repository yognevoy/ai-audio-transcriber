import {deleteFile, fetchFiles} from './api';

let loadedFiles = [];

export async function loadFiles() {
    const filesTab = document.getElementById('files-tab');
    if (!filesTab) return;

    filesTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
        <div class="flex justify-center items-center py-8">
            <svg class="animate-spin h-8 w-8 text-gray-600" viewBox="0 0 24 24"></svg>
        </div>
    `;

    try {
        const data = await fetchFiles();

        if (data.success) {
            loadedFiles = data.files;
            renderFilesList();
        } else {
            showError('Failed to load files');
        }
    } catch (error) {
        console.error(error);
        showError('Error loading files');
    }
}

function renderFilesList() {
    const filesTab = document.getElementById('files-tab');
    if (!filesTab) return;

    if (loadedFiles.length === 0) {
        filesTab.innerHTML = `
            <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
            <div class="text-center py-12">
                <p class="text-gray-500">No files uploaded yet</p>
            </div>
        `;
        return;
    }

    const filesHtml = loadedFiles.map(file => `
        <div class="p-4 border border-gray-200 rounded-lg" data-file-id="${file.id}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-medium text-[#1b1b18]">${file.filename}</h3>
                    <p class="text-sm text-[#706f6c]">
                        Size: ${file.size_formatted} | Uploaded: ${file.uploaded_at}
                    </p>
                </div>
                <button class="delete-btn px-3 py-1 text-sm bg-red-50 text-red-600 rounded"
                        data-file-id="${file.id}">
                    Delete
                </button>
            </div>
        </div>
    `).join('');

    filesTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
        <div class="space-y-4">${filesHtml}</div>
    `;

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () =>
            handleDeleteFile(btn.dataset.fileId)
        );
    });
}

async function handleDeleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this file?')) return;

    try {
        const data = await deleteFile(fileId);

        if (data.success) {
            loadedFiles = loadedFiles.filter(f => f.id != fileId);
            renderFilesList();
        } else {
            alert(data.message || 'Failed to delete file');
            await loadFiles();
        }
    } catch (error) {
        console.error(error);
        alert('Error deleting file');
        await loadFiles();
    }
}

function showError(message) {
    const filesTab = document.getElementById('files-tab');
    filesTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Uploaded Files</h2>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-red-700 text-sm">${message}</p>
        </div>
    `;
}
