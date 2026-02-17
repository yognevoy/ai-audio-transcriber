<script setup lang="ts">
import type { StatusMessageType } from '@/types/audio';
import { computed } from 'vue';

const props = defineProps<{
    type: StatusMessageType;
    message?: string;
    html?: string;
    show?: boolean;
}>();

const classes = computed(() => {
    const baseClasses = 'border rounded-md p-4 transition-all duration-300';

    switch (props.type) {
        case 'success':
            return `${baseClasses} bg-green-50 border-green-200`;
        case 'error':
            return `${baseClasses} bg-red-50 border-red-200`;
        case 'warning':
            return `${baseClasses} bg-yellow-50 border-yellow-200`;
        case 'info':
        default:
            return `${baseClasses} bg-blue-50 border-blue-200`;
    }
});

const textClasses = computed(() => {
    const baseClasses = 'text-sm font-medium';

    switch (props.type) {
        case 'success':
            return `${baseClasses} text-green-700`;
        case 'error':
            return `${baseClasses} text-red-700`;
        case 'warning':
            return `${baseClasses} text-yellow-700`;
        case 'info':
        default:
            return `${baseClasses} text-blue-700`;
    }
});
</script>

<template>
    <div v-if="show !== false" :class="classes">
        <p :class="textClasses">
            <slot name="title">
                {{ type.charAt(0).toUpperCase() + type.slice(1) }}
            </slot>
        </p>
        <p v-if="html" class="mt-2 text-gray-700 text-sm" v-html="html" />
        <p v-else-if="message" class="mt-2 text-gray-700 text-sm">
            {{ message }}
        </p>
        <slot />
    </div>
</template>
