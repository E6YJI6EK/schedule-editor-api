import { ref } from 'vue';

export type ToastType = 'error' | 'success' | 'info' | 'warning';

interface ToastMessage {
  id: number;
  message: string;
  type: ToastType;
  duration?: number;
}

const toasts = ref<ToastMessage[]>([]);
let idCounter = 0;

export function useToast() {
  const show = (message: string, type: ToastType = 'info', duration = 3000) => {
    const id = idCounter++;
    toasts.value.push({ id, message, type, duration });

    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }

    return id;
  };

  const remove = (id: number) => {
    const index = toasts.value.findIndex((t: ToastMessage) => t.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };

  const error = (message: string, duration = 5000) => {
    return show(message, 'error', duration);
  };

  const success = (message: string, duration = 3000) => {
    return show(message, 'success', duration);
  };

  const info = (message: string, duration = 3000) => {
    return show(message, 'info', duration);
  };

  const warning = (message: string, duration = 4000) => {
    return show(message, 'warning', duration);
  };

  return {
    toasts,
    show,
    remove,
    error,
    success,
    info,
    warning,
  };
}




