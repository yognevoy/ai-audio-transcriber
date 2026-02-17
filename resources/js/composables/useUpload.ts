import { ref, type Ref } from 'vue';
import type { UploadResponse, FileInfo } from '@/types/audio';

/**
 * Composable for handling file uploads
 */
export function useUpload() {
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const successMessage = ref<string | null>(null);

    /**
     * Upload an audio file to the server
     */
    async function uploadFile(file: File): Promise<UploadResponse> {
        isLoading.value = true;
        error.value = null;
        successMessage.value = null;

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

            if (data.success) {
                successMessage.value = 'File uploaded successfully';
            } else {
                error.value = data.message || 'An error occurred during upload';
            }

            return data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Network error occurred. Please try again.';
            return {
                success: false,
                message: error.value,
            };
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clear the current status messages
     */
    function clearStatus(): void {
        error.value = null;
        successMessage.value = null;
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
        error,
        successMessage,
        uploadFile,
        clearStatus,
        formatFileSize,
    };
}
