<script setup lang="ts">
import type { TabType } from '@/types/audio';

defineProps<{
    tab: TabType;
    activeTab: TabType;
}>();

defineEmits<{
    (e: 'click', tab: TabType): void;
}>();
</script>

<template>
    <button
        :class="[
            'tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent cursor-pointer transition-all duration-200 ease-in-out',
            activeTab === tab
                ? 'active-tab text-[#1b1b18] border-[#1b1b18]'
                : 'text-[#706f6c] hover:text-[#1b1b18] hover:border-gray-300',
        ]"
        @click="$emit('click', tab)"
    >
        <slot />
    </button>
</template>

<style scoped>
.active-tab {
    color: #1b1b18 !important;
    border-bottom-color: #1b1b18 !important;
}
</style>
