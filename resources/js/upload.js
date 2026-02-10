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
});

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
        });
    });
}

initTabs();
