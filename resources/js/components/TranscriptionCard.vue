<script setup lang="ts">
import { inject } from 'vue';
import type { Transcription } from '@/types/audio';

const props = defineProps<{
    transcription: Transcription;
}>();

const toast = inject<{ show: (message: string) => void }>('toast');

function getStatusText(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

/**
 * Copy the full transcription content to clipboard
 */
async function copyToClipboard(): Promise<void> {
    const content = props.transcription.content || 'No content available';

    await navigator.clipboard.writeText(content);
    if (toast) {
        toast.show('Copied!');
    }
}
</script>

<template>
    <div class="p-4 border border-gray-200 rounded-lg bg-white">
        <div class="flex justify-between items-start">
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-[#1b1b18] truncate" :title="transcription.filename">
                    Transcription of {{ transcription.filename }}
                </h3>
                <p class="text-sm text-[#706f6c] mt-1">
                    Status:
                    <span class="text-[#706f6c]">
                        {{ getStatusText(transcription.status) }}
                    </span>
                    | Created: {{ transcription.created_at }}
                </p>
                <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                    {{ transcription.content || 'No content available' }}
                </p>
                <p v-if="transcription.error_message" class="mt-2 text-sm text-red-600">
                    Error: {{ transcription.error_message }}
                </p>
            </div>
            <div class="flex space-x-2 ml-4 shrink-0">
                <template v-if="transcription.status === 'failed'">
                    <button
                        class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded cursor-pointer hover:bg-gray-200 transition-colors duration-200"
                    >
                        Retry
                    </button>
                </template>
                <template v-else>
                    <button
                        class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded cursor-pointer hover:bg-gray-200 transition-colors duration-200"
                    >
                        View
                    </button>
                </template>
                <button
                    class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded cursor-pointer hover:bg-gray-200 transition-colors duration-200 flex items-center gap-1.5"
                    @click="copyToClipboard"
                    type="button"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
