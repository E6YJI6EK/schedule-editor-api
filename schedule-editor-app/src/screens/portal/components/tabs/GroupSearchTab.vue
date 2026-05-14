<script setup lang="ts">
import { ref, watch } from 'vue'
import { Search } from 'lucide-vue-next'
import { searchGroupsByName } from '@/api/groups'
import type { Group } from '@/api/types'
import PublicScheduleModal from '../PublicScheduleModal.vue'

const query = ref('')
const groups = ref<Group[]>([])
const loading = ref(false)

const selectedGroup = ref<Group | null>(null)
const modalOpen = ref(false)

let debounceTimer: ReturnType<typeof setTimeout>

watch(query, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchGroups(val), 300)
}, { immediate: true })

async function fetchGroups(name: string) {
  loading.value = true
  try {
    const res = await searchGroupsByName({ name: name || undefined })
    groups.value = res.data
  } catch {
    groups.value = []
  } finally {
    loading.value = false
  }
}

function openGroup(group: Group) {
  selectedGroup.value = group
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
        placeholder="Введите название группы..."
        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
      />
    </div>

    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600" />
    </div>

    <div v-else-if="groups.length === 0" class="text-center py-8 text-gray-400 text-sm">
      Группы не найдены
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
      <button
        v-for="group in groups"
        :key="group.id"
        class="p-3 bg-white border border-gray-200 rounded-xl text-left hover:border-blue-400 hover:shadow-sm transition-all group"
        @click="openGroup(group)"
      >
        <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
          {{ group.name }}
        </span>
      </button>
    </div>
  </div>

  <PublicScheduleModal
    v-if="selectedGroup"
    v-model:open="modalOpen"
    :entity-id="selectedGroup.id"
    :entity-name="selectedGroup.name"
    entity-type="group"
  />
</template>
