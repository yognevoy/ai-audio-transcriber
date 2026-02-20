import { ref } from 'vue';
import type { UploadResponse, FileProcessingStatus } from '@/types/audio';

/**
 * Composable for handling file uploads
 */
export function useUpload() {
    const isLoading = ref(false);
    const processingStatus = ref<FileProcessingStatus | null>(null);
    const pollInterval = ref<number | null>(null);

    /**
     * Poll for file processing status
     */
    function startPolling(fileId: string | number): void {
        processingStatus.value = null;

        const poll = async () => {
            try {
                const response = await fetch(`/api/files/${fileId}/status`);
                const data = await response.json();

                if (data.success) {
                    processingStatus.value = data.file;

                    // Stop polling if completed or failed
                    if (
                        data.file.progress === 100 ||
                        data.file.stage === 'failed' ||
                        data.file.stage === 'cleaning_failed'
                    ) {
                        stopPolling();
                    }
                }
            } catch (err) {
                console.error('Error polling status:', err);
            }
        };

        // Initial poll
        poll();

        // Continue polling every 2 seconds
        pollInterval.value = window.setInterval(poll, 2000);
    }

    /**
     * Stop polling for file status
     */
    function stopPolling(): void {
        if (pollInterval.value) {
            clearInterval(pollInterval.value);
            pollInterval.value = null;
        }
    }

    /**
     * Upload an audio file to the server
     */
    async function uploadFile(file: File): Promise<UploadResponse> {
        isLoading.value = true;
        processingStatus.value = null;

        const formData = new FormData();
        formData.append('audio_file', file);

        try {
            const response = await fetch('/api/upload', {
                method: 'POST',
                body: formData,
            });

            const data: UploadResponse = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Upload failed');
            }

            if (data.success && data.file_info) {
                // Start polling for processing status
                startPolling(data.file_info.id);
            } else {
                const errorMessage = data.message || 'An error occurred during upload';
                alert(errorMessage);
            }

            return data;
        } catch (err) {
            const errorMessage = err instanceof Error ? err.message : 'Network error occurred. Please try again.';
            alert(errorMessage);
            return {
                success: false,
                message: errorMessage,
            };
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clear the current status messages
     */
    function clearStatus(): void {
        stopPolling();
        processingStatus.value = null;
    }

    /**
     * Format file size for display
     */
    function formatFileSize(file: File): string {
        const sizeInMB = (file.size / 1024 / 1024).toFixed(2);
        return `${file.name} (${sizeInMB} MB)`;
    }

    return {
        isLoading,
        processingStatus,
        uploadFile,
        clearStatus,
        formatFileSize,
        stopPolling,
    };
}
