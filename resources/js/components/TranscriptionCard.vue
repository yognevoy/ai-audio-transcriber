<script setup lang="ts">
import type { Transcription, TranscriptionStatus } from '@/types/audio';

const props = defineProps<{
    transcription: Transcription;
}>();

function getStatusText(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
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
                    class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded cursor-pointer hover:bg-gray-200 transition-colors duration-200"
                >
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
