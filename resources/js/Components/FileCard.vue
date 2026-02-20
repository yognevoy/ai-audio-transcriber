<script setup lang="ts">
import type { AudioFile } from '@/types/audio';

defineProps<{
    file: AudioFile;
}>();

defineEmits<{
    (e: 'delete', fileId: number | string): void;
}>();
</script>

<template>
    <div class="p-4 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-shadow duration-200 min-h-[100px]">
        <div class="flex justify-between items-center">
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-[#1b1b18] truncate" :title="file.filename">
                    {{ file.filename }}
                </h3>
                <p class="text-sm text-[#706f6c] mt-1">
                    Size: {{ file.size_formatted }} | Uploaded: {{ file.uploaded_at }}
                </p>
            </div>
            <button
                class="delete-btn ml-4 px-3 py-1 text-sm bg-red-50 text-red-600 rounded cursor-pointer hover:bg-red-100 transition-colors duration-200"
                @click="$emit('delete', file.id)"
            >
                Delete
            </button>
        </div>
    </div>
</template>
