import { fetchTranscriptions } from './api';

let loadedTranscriptions = [];

export async function loadTranscriptions() {
    const transcriptionsTab = document.getElementById('transcriptions-tab');
    if (!transcriptionsTab) return;

    transcriptionsTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Transcriptions</h2>
        <div class="flex justify-center items-center py-8">
            <svg class="animate-spin h-8 w-8 text-gray-600" viewBox="0 0 24 24"></svg>
        </div>
    `;

    try {
        const data = await fetchTranscriptions();

        if (data.success) {
            loadedTranscriptions = data.transcriptions;
            renderTranscriptionsList();
        } else {
            showError('Failed to load transcriptions');
        }
    } catch (error) {
        console.error(error);
        showError('Error loading transcriptions');
    }
}

function renderTranscriptionsList() {
    const transcriptionsTab = document.getElementById('transcriptions-tab');
    if (!transcriptionsTab) return;

    if (loadedTranscriptions.length === 0) {
        transcriptionsTab.innerHTML = `
            <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Transcriptions</h2>
            <div class="text-center py-12">
                <p class="text-gray-500">No transcriptions yet</p>
            </div>
        `;
        return;
    }

    const transcriptionsHtml = loadedTranscriptions.map(transcription => {
        const buttons = getButtons(transcription.status);

        return `
            <div class="p-4 border border-gray-200 rounded-lg" data-transcription-id="${transcription.transcription_id}">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="font-medium text-[#1b1b18]">Transcription of ${transcription.filename}</h3>
                        <p class="text-sm text-[#706f6c] mt-1">Status: ${capitalizeFirstLetter(transcription.status)} | Created: ${transcription.created_at}</p>
                        <p class="mt-2 text-sm text-[#1b1b18] line-clamp-2">
                            ${transcription.content || 'No content available'}
                        </p>
                    </div>
                    <div class="flex space-x-2 ml-4">
                        ${buttons}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    transcriptionsTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Transcriptions</h2>
        <div class="space-y-4">${transcriptionsHtml}</div>
    `;
}

function getButtons(status) {
    if (status === 'failed') {
        return `
            <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200">Retry</button>
            <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
        `;
    }

    return `
        <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">View</button>
        <button class="px-3 py-1 text-sm bg-gray-100 text-[#1b1b18] rounded hover:bg-gray-200 cursor-pointer">Copy</button>
    `;
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function showError(message) {
    const transcriptionsTab = document.getElementById('transcriptions-tab');
    transcriptionsTab.innerHTML = `
        <h2 class="text-[#1b1b18] text-lg font-medium mb-6">Transcriptions</h2>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-red-700 text-sm">${message}</p>
        </div>
    `;
}
