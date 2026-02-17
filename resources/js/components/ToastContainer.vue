<script setup lang="ts">
import type { Toast } from '@/composables/useToast';

defineProps<{
    toasts: Toast[];
}>();
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-x-full opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-x-0 opacity-100"
            leave-to-class="transform translate-x-full opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="px-4 py-3 rounded-lg shadow-lg text-white min-w-[280px] bg-black/80 backdrop-blur-sm"
            >
                <p class="text-sm font-medium">{{ toast.message }}</p>
            </div>
        </TransitionGroup>
    </div>
</template>
