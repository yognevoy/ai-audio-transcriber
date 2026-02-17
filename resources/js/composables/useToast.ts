import { ref, type Ref } from 'vue';

/**
 * Toast notification interface
 */
export interface Toast {
    id: number;
    message: string;
    duration?: number;
}

/**
 * Composable for managing toast notifications
 * Provides a centralized way to show/hide toast messages
 * Multiple toasts can be shown stacked vertically
 */
export function useToast() {
    const toasts: Ref<Toast[]> = ref([]);
    let toastId = 0;
    const timeoutIds: Map<number, ReturnType<typeof setTimeout>> = new Map();

    /**
     * Show a toast notification
     */
    function show(message: string, duration: number = 3000): void {
        const id = ++toastId;
        const toast: Toast = { id, message, duration };

        toasts.value.push(toast);

        if (duration > 0) {
            const timeoutId = setTimeout(() => {
                const index = toasts.value.findIndex((t) => t.id === id);
                if (index !== -1) {
                    toasts.value.splice(index, 1);
                }
                timeoutIds.delete(id);
            }, duration);
            timeoutIds.set(id, timeoutId);
        }
    }

    return {
        toasts,
        show,
    };
}
