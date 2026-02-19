import { ref, type Ref } from 'vue';
import type { Transcription, TranscriptionsResponse } from '@/types/audio';

/**
 * Composable for managing transcriptions
 */
export function useTranscriptions() {
    const transcriptions = ref<Transcription[]>([]);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    /**
     * Fetch all transcriptions from the server
     */
    async function fetchTranscriptions(): Promise<void> {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/transcriptions');
            const data: TranscriptionsResponse = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to fetch transcriptions');
            }

            if (data.success) {
                transcriptions.value = data.transcriptions;
            } else {
                error.value = data.message || 'Failed to load transcriptions';
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Error loading transcriptions';
            transcriptions.value = [];
        } finally {
            isLoading.value = false;
        }
    }

    /**
     * Clear the error state
     */
    function clearError(): void {
        error.value = null;
    }

    /**
     * Refresh the transcriptions list
     */
    async function refresh(): Promise<void> {
        await fetchTranscriptions();
    }

    /**
     * Get the display text for a transcription status
     */
    function getStatusText(status: string): string {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    return {
        transcriptions,
        isLoading,
        error,
        fetchTranscriptions,
        clearError,
        refresh,
        getStatusText,
    };
}
