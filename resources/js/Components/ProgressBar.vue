<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    progress: number;
    isFinished: boolean;
    isSuccessful: boolean;
    errorMessage?: string | null;
}

const props = defineProps<Props>();

const statusLabel = computed(() => {
    if (!props.isFinished) {
        return 'Processing...';
    }

    return props.isSuccessful
        ? 'Completed!'
        : 'Processing failed';
});
</script>

<template>
    <div class="w-full">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-[#1b1b18]">{{ statusLabel }}</span>
            <span v-if="!isFinished" class="text-sm text-gray-600">{{ progress }}%</span>
        </div>
        <div v-if="!isFinished" class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
            <div
                class="h-2.5 rounded-full transition-all duration-500 ease-out bg-black"
                :style="{ width: `${progress}%` }"
            >
                <div
                    class="h-full w-full animate-pulse bg-gradient-to-r from-transparent via-white/30 to-transparent"
                />
            </div>
        </div>
    </div>
</template>
