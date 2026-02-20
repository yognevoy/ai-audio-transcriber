<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import TabButton from '@/Components/TabButton.vue';
import FileCard from '@/Components/FileCard.vue';
import TranscriptionCard from '@/Components/TranscriptionCard.vue';
import FileUploadZone from '@/Components/FileUploadZone.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { useUpload } from '@/Composables/useUpload';
import { useFiles } from '@/Composables/useFiles';
import { useTranscriptions } from '@/Composables/useTranscriptions';
import type { TabType } from '@/types/audio';

// Tab state
const activeTab = ref<TabType>('upload');

// File upload state
const selectedFile = ref<File | null>(null);
const {
    isLoading: isUploading,
    processingStatus,
    uploadFile,
    clearStatus,
} = useUpload();

// Files state
const {
    files,
    isLoading: isLoadingFiles,
    error: filesError,
    fetchFiles,
    deleteFile: deleteFileApi,
    clearError: clearFilesError,
} = useFiles();

// Transcriptions state
const {
    transcriptions,
    isLoading: isLoadingTranscriptions,
    error: transcriptionsError,
    fetchTranscriptions,
    clearError: clearTranscriptionsError,
} = useTranscriptions();

// Methods
async function handleUpload() {
    if (!selectedFile.value) {
        alert('Please select an audio file first');
        return;
    }

    const result = await uploadFile(selectedFile.value);

    if (result.success) {
        // Refresh files list after successful upload
        await fetchFiles();
        // Reset selected file
        selectedFile.value = null;
    }
}

async function handleDeleteFile(fileId: number | string) {
    if (!confirm('Are you sure you want to delete this file?')) return;

    const success = await deleteFileApi(fileId);
    if (!success) {
        // Refresh files list if delete failed
        await fetchFiles();
    }
}

// Watch for tab changes to load data
watch(activeTab, (newTab) => {
    if (newTab === 'files') {
        fetchFiles();
    } else if (newTab === 'transcriptions') {
        fetchTranscriptions();
    }
    // Clear status messages when switching tabs
    clearStatus();
    clearFilesError();
    clearTranscriptionsError();
});
</script>

<template>
    <Head title="Audio Transcriber" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
        <div class="w-full max-w-[800px] lg:w-[900px] lg:max-w-4xl">
            <div
                class="bg-white rounded-t-lg lg:rounded-t-none lg:rounded-tl-lg lg:rounded-r-lg shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] border border-[#e3e3e0] overflow-hidden min-h-[80vh]"
            >

                <!-- Header with User Info -->
                <div class="border-b border-gray-200 px-6 py-3 flex items-center justify-between bg-gray-50">
                    <span class="text-sm text-[#1b1b18] font-medium">{{ $page.props.auth.user.name }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-600 hover:text-black transition-colors duration-200"
                    >
                        Log Out
                    </Link>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <TabButton tab="upload" :active-tab="activeTab" @click="activeTab = $event">
                            Upload
                        </TabButton>
                        <TabButton tab="files" :active-tab="activeTab" @click="activeTab = $event">
                            Files
                        </TabButton>
                        <TabButton
                            tab="transcriptions"
                            :active-tab="activeTab"
                            @click="activeTab = $event"
                        >
                            Transcriptions
                        </TabButton>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6 lg:p-8 h-[calc(80vh-100px)] overflow-y-auto">
                    <!-- Upload Tab Content -->
                    <div v-show="activeTab === 'upload'" class="tab-content">
                        <h1 class="text-[#1b1b18] text-lg font-medium mb-6">
                            AI Audio Transcriber
                        </h1>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-[#1b1b18] mb-2">
                                Upload Audio File
                            </label>
                            <FileUploadZone
                                v-model="selectedFile"
                                :disabled="isUploading"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <button
                                class="relative overflow-hidden w-full px-5 py-2.5 text-sm font-medium rounded-lg border border-black text-white bg-black cursor-pointer transition-transform duration-200 active:scale-[0.97] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 before:absolute before:inset-0 before:bg-gradient-to-r before:from-gray-800 before:to-black before:opacity-0 before:transition-opacity before:duration-300 hover:before:opacity-100 disabled:opacity-70 disabled:cursor-not-allowed"
                                @click="handleUpload"
                            >
                                <span
                                    v-show="!isUploading"
                                    class="btn-text relative z-10"
                                >
                                    Transcribe Audio
                                </span>
                                <span
                                    v-show="isUploading"
                                    class="btn-loader relative z-10 flex items-center justify-center gap-2"
                                >
                                    <svg
                                        class="animate-spin h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                        />
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="processingStatus" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="mb-3">
                                <p class="text-sm font-medium text-[#1b1b18] mb-1">
                                    Processing: {{ processingStatus.filename }}
                                </p>
                            </div>
                            <ProgressBar
                                :progress="processingStatus.progress"
                                :is-finished="processingStatus.is_finished"
                                :is-successful="processingStatus.is_successful"
                                :error-message="processingStatus.error_message"
                            />
                        </div>
                    </div>

                    <!-- Files Tab Content -->
                    <div v-show="activeTab === 'files'" class="tab-content">
                        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">
                            Uploaded Files
                        </h2>

                        <div v-if="isLoadingFiles" class="text-center py-12">
                            <p class="text-gray-500">Loading files...</p>
                        </div>

                        <div v-else-if="files.length === 0" class="text-center py-12">
                            <p class="text-gray-500">No files uploaded yet</p>
                        </div>

                        <div v-else class="space-y-4">
                            <FileCard
                                v-for="file in files"
                                :key="file.id"
                                :file="file"
                                @delete="handleDeleteFile"
                            />
                        </div>
                    </div>

                    <!-- Transcriptions Tab Content -->
                    <div v-show="activeTab === 'transcriptions'" class="tab-content">
                        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">
                            Transcriptions
                        </h2>

                        <div v-if="isLoadingTranscriptions" class="text-center py-12">
                            <p class="text-gray-500">Loading transcriptions...</p>
                        </div>

                        <div v-else-if="transcriptions.length === 0" class="text-center py-12">
                            <p class="text-gray-500">No transcriptions yet</p>
                        </div>

                        <div v-else class="space-y-4">
                            <TranscriptionCard
                                v-for="transcription in transcriptions"
                                :key="transcription.transcription_id"
                                :transcription="transcription"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@import '../../css/upload.css';
</style>
