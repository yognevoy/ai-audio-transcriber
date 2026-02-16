export async function uploadFile(file) {
    const formData = new FormData();
    formData.append('audio_file', file);

    const response = await fetch('/api/upload', {
        method: 'POST',
        body: formData,
    });

    return response.json();
}

export async function fetchFiles() {
    const response = await fetch('/api/files');
    return response.json();
}

export async function deleteFile(fileId) {
    const response = await fetch(`/api/files/${fileId}`, {
        method: 'DELETE'
    });

    return response.json();
}
