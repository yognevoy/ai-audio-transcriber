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

const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');

tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        const tabName = button.getAttribute('data-tab');

        tabButtons.forEach(btn => {
            btn.classList.remove('active-tab');
            btn.classList.remove('border-[#1b1b18]');
            btn.classList.add('border-transparent');
            btn.classList.add('text-[#706f6c]');
        });

        button.classList.add('active-tab');
        button.classList.remove('border-transparent');
        button.classList.remove('text-[#706f6c]');
        button.classList.add('border-[#1b1b18]');
        button.classList.add('text-[#1b1b18]');

        const currentVisibleTab = document.querySelector('.tab-content:not(.hidden)');

        if (currentVisibleTab) {
            currentVisibleTab.style.opacity = '0';
            currentVisibleTab.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                currentVisibleTab.classList.add('hidden');

                const newTab = document.getElementById(`${tabName}-tab`);
                newTab.classList.remove('hidden');

                newTab.style.opacity = '0';
                newTab.style.transform = 'translateY(10px)';

                void newTab.offsetWidth;

                newTab.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                newTab.style.opacity = '1';
                newTab.style.transform = 'translateY(0)';
            }, 200);
        } else {
            const newTab = document.getElementById(`${tabName}-tab`);
            newTab.classList.remove('hidden');
            newTab.style.opacity = '1';
            newTab.style.transform = 'translateY(0)';
        }
    });
});
