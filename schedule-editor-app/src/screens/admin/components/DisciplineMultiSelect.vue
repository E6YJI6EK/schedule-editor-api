<script setup lang="ts">
import { ref, computed } from 'vue'
import { X } from 'lucide-vue-next'
import type { Discipline } from '@/api/types'

const props = defineProps<{
  modelValue: number[]
  disciplines: Discipline[]
}>()

const emit = defineEmits<{
  'update:modelValue': [ids: number[]]
}>()

const query = ref('')
const isOpen = ref(false)

const selectedDisciplines = computed(() =>
  props.disciplines.filter(d => props.modelValue.includes(d.id))
)

const filteredOptions = computed(() => {
  const q = query.value.toLowerCase().trim()
  return props.disciplines.filter(d =>
    !props.modelValue.includes(d.id) &&
    (!q || d.name.toLowerCase().includes(q))
  )
})

const add = (d: Discipline) => {
  emit('update:modelValue', [...props.modelValue, d.id])
  query.value = ''
  isOpen.value = false
}

const remove = (id: number) => {
  emit('update:modelValue', props.modelValue.filter(x => x !== id))
}

const handleBlur = () => {
  setTimeout(() => { isOpen.value = false }, 150)
}
</script>

<template>
  <div>
    <div v-if="selectedDisciplines.length > 0" class="flex flex-wrap gap-1.5 mb-2">
      <span
        v-for="d in selectedDisciplines"
        :key="d.id"
        class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md text-xs font-medium"
      >
        {{ d.name }}
        <button type="button" @click="remove(d.id)" class="hover:text-blue-600 transition-colors">
          <X class="w-3 h-3" />
        </button>
      </span>
    </div>

    <div class="relative">
      <input
        v-model="query"
        type="text"
        placeholder="Поиск дисциплины..."
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        @focus="isOpen = true"
        @blur="handleBlur"
      />

      <div
        v-if="isOpen && filteredOptions.length > 0"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto"
      >
        <div
          v-for="d in filteredOptions"
          :key="d.id"
          @mousedown.prevent="add(d)"
          class="px-3 py-2 text-sm hover:bg-gray-50 cursor-pointer"
        >
          {{ d.name }}
        </div>
      </div>

      <div
        v-else-if="isOpen && query && filteredOptions.length === 0"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg"
      >
        <div class="px-3 py-2 text-sm text-gray-400">Не найдено</div>
      </div>
    </div>
  </div>
</template>
