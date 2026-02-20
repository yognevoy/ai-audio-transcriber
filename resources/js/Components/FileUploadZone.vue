<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    modelValue?: File | null;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | null): void;
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const selectedFile = computed(() => props.modelValue);

const fileInfo = computed(() => {
    if (!selectedFile.value) return '';
    const sizeInMB = (selectedFile.value.size / 1024 / 1024).toFixed(2);
    return `Selected: ${selectedFile.value.name} (${sizeInMB} MB)`;
});

function handleDragOver(e: DragEvent) {
    e.preventDefault();
    if (!props.disabled) {
        isDragging.value = true;
    }
}

function handleDragLeave(e: DragEvent) {
    e.preventDefault();
    isDragging.value = false;
}

function handleDrop(e: DragEvent) {
    e.preventDefault();
    isDragging.value = false;

    if (props.disabled) return;

    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        const file = files[0];
        if (file.type.startsWith('audio/')) {
            emit('update:modelValue', file);
        }
    }
}

function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        emit('update:modelValue', files[0]);
    }
}

function handleClick() {
    if (!props.disabled && fileInput.value) {
        fileInput.value.click();
    }
}
</script>

<template>
    <div class="w-full">
        <div
            :class="[
                'flex items-center justify-center w-full h-64 border-2 border-dashed rounded-lg transition-colors duration-200',
                isDragging
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 bg-gray-50 hover:bg-gray-100'
            ]"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            @click="handleClick"
        >
            <label class="flex flex-col items-center justify-center cursor-pointer w-full h-full pt-5 pb-6">
                <svg
                    class="w-8 h-8 mb-4 text-[#706f6c]"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 16"
                >
                    <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"
                    />
                </svg>
                <p class="mb-2 text-sm text-[#706f6c]">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs text-[#706f6c]">MP3, WAV, FLAC, M4A (MAX. 100MB)</p>
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept="audio/*"
                    :disabled="disabled"
                    @change="handleFileChange"
                />
            </label>
        </div>
        <p v-if="fileInfo" class="mt-2 text-sm text-[#706f6c]">
            {{ fileInfo }}
        </p>
    </div>
</template>
