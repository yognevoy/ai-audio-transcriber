import { loadFiles } from './files';
import { loadTranscriptions } from './transcriptions';

export function initTabs() {
    const buttons = document.querySelectorAll('.tab-button');
    const tabs = document.querySelectorAll('.tab-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.dataset.tab;

            buttons.forEach(b => b.classList.remove('active-tab'));
            button.classList.add('active-tab');

            tabs.forEach(tab => tab.classList.add('hidden'));

            const activeTab = document.getElementById(`${tabName}-tab`);
            activeTab?.classList.remove('hidden');

            if (tabName === 'files') {
                loadFiles();
            }

            if (tabName === 'transcriptions') {
                loadTranscriptions();
            }
        });
    });
}
