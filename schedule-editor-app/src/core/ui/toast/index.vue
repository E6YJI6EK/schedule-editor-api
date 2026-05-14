<template>
  <div class="fixed bottom-4 right-4 z-50 space-y-2">
    <Toast
      v-for="toast in toasts.toasts.value"
      :key="toast.id"
      :message="toast.message"
      :type="toast.type"
      :duration="0"
      @close="toasts.remove(toast.id)"
    />
  </div>
</template>

<script setup lang="ts">
import Toast from './view.vue';
import { useToast } from './model';

const toasts = useToast();
</script>




