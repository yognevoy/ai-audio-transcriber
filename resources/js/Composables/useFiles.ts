import { ref, type Ref } from 'vue';
import type { AudioFile, FilesResponse, DeleteResponse } from '@/types/audio';

/**
 * Composable for managing audio files
 */
export function useFiles() {
    const files = ref<AudioFile[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    /**
     * Fetch all uploaded files from the server
     */
    async function fetchFiles(): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/files');
            const data: FilesResponse = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to fetch files');
            }

            if (data.success) {
                files.value = data.files;
            } else {
                error.value = data.message || 'Failed to load files';
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Error loading files';
            files.value = [];
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Delete a file by ID
     */
    async function deleteFile(fileId: number | string): Promise<boolean> {
        try {
            const response = await fetch(`/api/files/${fileId}`, {
                method: 'DELETE',
            });

            const data: DeleteResponse = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete file');
            }

            if (data.success) {
                files.value = files.value.filter((f) => f.id !== fileId);
                return true;
            } else {
                error.value = data.message || 'Failed to delete file';
                return false;
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Error deleting file';
            return false;
        }
    }

    /**
     * Clear the error state
     */
    function clearError(): void {
        error.value = null;
    }

    /**
     * Refresh the files list
     */
    async function refresh(): Promise<void> {
        await fetchFiles();
    }

    return {
        files,
        isLoading,
        error,
        fetchFiles,
        deleteFile,
        clearError,
        refresh,
    };
}
