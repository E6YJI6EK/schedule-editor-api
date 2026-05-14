<script setup lang="ts">
import { ref, watch } from 'vue'
import { Search } from 'lucide-vue-next'
import { searchTeachersByName } from '@/api/teachers'
import type { Teacher } from '@/api/types'
import PublicScheduleModal from '../PublicScheduleModal.vue'

const query = ref('')
const teachers = ref<Teacher[]>([])
const loading = ref(false)

const selectedTeacher = ref<Teacher | null>(null)
const modalOpen = ref(false)

let debounceTimer: ReturnType<typeof setTimeout>

watch(query, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchTeachers(val), 300)
}, { immediate: true })

async function fetchTeachers(name: string) {
  loading.value = true
  try {
    const res = await searchTeachersByName({ name: name || undefined })
    teachers.value = res.data
  } catch {
    teachers.value = []
  } finally {
    loading.value = false
  }
}

function openTeacher(teacher: Teacher) {
  selectedTeacher.value = teacher
  modalOpen.value = true
}
</script>

<template>
  <div class="space-y-4">
    <div class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="18" />
      <input
        v-model="query"
        type="text"
        placeholder="Введите ФИО преподавателя..."
        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
      />
    </div>

    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600" />
    </div>

    <div v-else-if="teachers.length === 0" class="text-center py-8 text-gray-400 text-sm">
      Преподаватели не найдены
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      <button
        v-for="teacher in teachers"
        :key="teacher.id"
        class="p-4 bg-white border border-gray-200 rounded-xl text-left hover:border-blue-400 hover:shadow-sm transition-all group"
        @click="openTeacher(teacher)"
      >
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <span class="text-blue-600 font-semibold text-sm">
              {{ teacher.name.charAt(0) }}
            </span>
          </div>
          <span class="text-sm font-medium text-gray-800 group-hover:text-blue-600 transition-colors leading-tight">
            {{ teacher.name }}
          </span>
        </div>
      </button>
    </div>
  </div>

  <PublicScheduleModal
    v-if="selectedTeacher"
    v-model:open="modalOpen"
    :entity-id="selectedTeacher.id"
    :entity-name="selectedTeacher.name"
    entity-type="teacher"
  />
</template>
