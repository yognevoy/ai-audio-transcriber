<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    progress: number;
    stage: string;
}

const props = defineProps<Props>();

const stageLabels: Record<string, string> = {
    pending: 'Preparing...',
    transcribing: 'Transcribing audio...',
    cleaning: 'Cleaning transcription...',
    completed: 'Completed!',
    failed: 'Processing failed',
    cleaning_failed: 'Cleaning failed',
};

const stageLabel = computed(() => {
    return stageLabels[props.stage] || 'Processing...';
});
</script>

<template>
    <div class="w-full">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-[#1b1b18]">{{ stageLabel }}</span>
            <span v-if="progress < 100" class="text-sm text-gray-600">{{ progress }}%</span>
        </div>
        <div v-if="progress < 100" class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div
                class="h-2.5 rounded-full transition-all duration-500 ease-out bg-black"
                :style="{ width: `${progress}%` }"
            >
                <div
                    v-if="progress < 100 && stage !== 'failed' && stage !== 'cleaning_failed'"
                    class="h-full w-full animate-pulse bg-gradient-to-r from-transparent via-white/30 to-transparent"
                />
            </div>
        </div>
    </div>
</template>
