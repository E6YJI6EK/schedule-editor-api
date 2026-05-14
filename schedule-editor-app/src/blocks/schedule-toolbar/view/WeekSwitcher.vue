<template>
  <div class="flex items-center space-x-4">
    <span class="text-sm font-medium text-gray-700">Неделя:</span>
    <div class="flex items-center space-x-2">
      <span :class="currentWeek === 'upper' ? 'text-blue-600 font-semibold' : 'text-gray-500'">
        Верхняя
      </span>
      <button
        @click="switchWeek"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        :class="currentWeek === 'lower' ? 'bg-blue-600' : 'bg-gray-200'"
      >
        <span
          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
          :class="currentWeek === 'lower' ? 'translate-x-6' : 'translate-x-1'"
        />
      </button>
      <span :class="currentWeek === 'lower' ? 'text-blue-600 font-semibold' : 'text-gray-500'">
        Нижняя
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">

interface Props {
  currentWeek: string
  switchWeek: () => void
}

const { currentWeek, switchWeek } = defineProps<Props>();
</script>
