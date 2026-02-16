import {uploadFile} from './api';
import {loadFiles} from './files';

export function initUpload() {
    const fileInput = document.getElementById('audio_file');
    const fileInfo = document.getElementById('file-info');
    const dropZone = document.querySelector('.drop-zone');
    const uploadBtn = document.getElementById('upload-btn');

    const resultDiv = document.getElementById('result');
    const statusText = document.getElementById('status-text');
    const resultMessage = document.getElementById('result-message');

    if (!fileInput || !uploadBtn) return;

    dropZone?.addEventListener('dragover', e => e.preventDefault());

    dropZone?.addEventListener('drop', e => {
        e.preventDefault();

        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            fileInput.files = e.dataTransfer.files;
            showFileInfo(file);
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        } else {
            fileInfo.classList.add('hidden');
        }
    });

    uploadBtn.addEventListener('click', async () => {
        await handleUploadFile();
    });

    async function handleUploadFile() {
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
                showSuccess(`
                    <strong>File uploaded successfully!</strong><br>
                    Name: ${data.file_info.name}<br>
                    Size: ${data.file_info.size} bytes<br>
                    Extension: ${data.file_info.extension}
                `);

                await loadFiles();
            } else {
                showError(data.message || 'An error occurred during upload.');
            }
        } catch (error) {
            resultDiv.classList.remove('hidden');
            showError('Network error occurred. Please try again.');
            console.error(error);
        } finally {
            setLoading(false);
        }
    }

    function showFileInfo(file) {
        fileInfo.textContent =
            `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileInfo.classList.remove('hidden');
    }

    function setLoading(isLoading) {
        uploadBtn.disabled = isLoading;
        uploadBtn.classList.toggle('opacity-70', isLoading);
        uploadBtn.classList.toggle('cursor-not-allowed', isLoading);

        uploadBtn.querySelector('.btn-text')
            ?.classList.toggle('hidden', isLoading);

        uploadBtn.querySelector('.btn-loader')
            ?.classList.toggle('hidden', !isLoading);
    }

    function showSuccess(messageHtml) {
        statusText.textContent = 'Success';
        document.getElementById('result-container').className =
            'bg-green-50 border border-green-200 rounded-md p-4';
        document.getElementById('status-text-element').className =
            'text-green-700 text-sm font-medium';
        resultMessage.innerHTML = messageHtml;
    }

    function showError(message) {
        statusText.textContent = 'Error';
        document.getElementById('result-container').className =
            'bg-red-50 border border-red-200 rounded-md p-4';
        document.getElementById('status-text-element').className =
            'text-red-700 text-sm font-medium';
        resultMessage.textContent = message;
    }
}
