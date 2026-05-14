<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps<{
  page: number
  total: number
  perPage: number
}>()

const emit = defineEmits<{
  'update:page': [page: number]
}>()

const totalPages = computed(() => Math.ceil(props.total / props.perPage))

const visiblePages = computed(() => {
  const pages: number[] = []
  const range = 2
  const start = Math.max(1, props.page - range)
  const end = Math.min(totalPages.value, props.page + range)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const go = (p: number) => {
  if (p >= 1 && p <= totalPages.value) emit('update:page', p)
}
</script>

<template>
  <div v-if="totalPages > 1" class="flex items-center justify-between pt-4 border-t border-gray-100">
    <p class="text-xs text-gray-500">
      {{ (page - 1) * perPage + 1 }}–{{ Math.min(page * perPage, total) }} из {{ total }}
    </p>

    <div class="flex items-center gap-1">
      <button
        @click="go(page - 1)"
        :disabled="page === 1"
        class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
      >
        <ChevronLeft class="w-4 h-4" />
      </button>

      <template v-if="visiblePages[0] > 1">
        <button @click="go(1)" class="w-7 h-7 text-xs rounded-md hover:bg-gray-100 text-gray-600 transition-colors">1</button>
        <span v-if="visiblePages[0] > 2" class="text-gray-400 text-xs px-0.5">…</span>
      </template>

      <button
        v-for="p in visiblePages"
        :key="p"
        @click="go(p)"
        class="w-7 h-7 text-xs rounded-md transition-colors"
        :class="p === page
          ? 'bg-blue-600 text-white font-medium'
          : 'hover:bg-gray-100 text-gray-600'"
      >
        {{ p }}
      </button>

      <template v-if="visiblePages[visiblePages.length - 1] < totalPages">
        <span v-if="visiblePages[visiblePages.length - 1] < totalPages - 1" class="text-gray-400 text-xs px-0.5">…</span>
        <button @click="go(totalPages)" class="w-7 h-7 text-xs rounded-md hover:bg-gray-100 text-gray-600 transition-colors">{{ totalPages }}</button>
      </template>

      <button
        @click="go(page + 1)"
        :disabled="page === totalPages"
        class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
      >
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>
