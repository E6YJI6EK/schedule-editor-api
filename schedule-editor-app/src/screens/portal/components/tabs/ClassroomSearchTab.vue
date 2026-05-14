<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Search } from 'lucide-vue-next'
import { searchBuildings } from '@/api/buildings'
import { searchClassRooms } from '@/api/classRooms'
import type { Building, ClassRoom } from '@/api/types'
import PublicScheduleModal from '../PublicScheduleModal.vue'

const buildings = ref<Building[]>([])
const selectedBuilding = ref<Building | null>(null)
const classRooms = ref<ClassRoom[]>([])
const roomQuery = ref('')
const loadingBuildings = ref(false)
const loadingRooms = ref(false)

const selectedRoom = ref<ClassRoom | null>(null)
const modalOpen = ref(false)

onMounted(async () => {
  loadingBuildings.value = true
  try {
    const res = await searchBuildings()
    buildings.value = res.data
    if (buildings.value.length > 0) {
      selectBuilding(buildings.value[0])
    }
  } finally {
    loadingBuildings.value = false
  }
})

let debounceTimer: ReturnType<typeof setTimeout>

watch(roomQuery, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchRooms, 300)
})

async function selectBuilding(building: Building) {
  selectedBuilding.value = building
  roomQuery.value = ''
  await fetchRooms()
}

async function fetchRooms() {
  if (!selectedBuilding.value) return
  loadingRooms.value = true
  try {
    const res = await searchClassRooms({
      building_id: selectedBuilding.value.id,
      number: roomQuery.value || undefined,
    })
    classRooms.value = res.data
  } catch {
    classRooms.value = []
  } finally {
    loadingRooms.value = false
  }
}

function openRoom(room: ClassRoom) {
  selectedRoom.value = room
  modalOpen.value = true
}
</script>

<template>
  <div class="flex gap-4 min-h-[400px]">
    <!-- Вертикальный таббар корпусов -->
    <div class="w-48 flex-shrink-0">
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-2">Корпуса</p>
      <div v-if="loadingBuildings" class="flex justify-center py-4">
        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600" />
      </div>
      <nav v-else class="flex flex-col gap-1">
        <button
          v-for="building in buildings"
          :key="building.id"
          :class="[
            'w-full px-3 py-2.5 text-left text-sm rounded-lg transition-all font-medium',
            selectedBuilding?.id === building.id
              ? 'bg-blue-600 text-white shadow-sm'
              : 'text-gray-700 hover:bg-gray-100'
          ]"
          @click="selectBuilding(building)"
        >
          {{ building.name }}
        </button>
      </nav>
    </div>

    <!-- Правая часть: поиск + грид аудиторий -->
    <div class="flex-1 space-y-3">
      <div v-if="selectedBuilding" class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" :size="16" />
        <input
          v-model="roomQuery"
          type="text"
          :placeholder="`Поиск аудитории в ${selectedBuilding.name}...`"
          class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        />
      </div>

      <div v-if="loadingRooms" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600" />
      </div>

      <div v-else-if="classRooms.length === 0" class="text-center py-8 text-gray-400 text-sm">
        Аудитории не найдены
      </div>

      <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2">
        <button
          v-for="room in classRooms"
          :key="room.id"
          class="p-3 bg-white border border-gray-200 rounded-xl text-center hover:border-blue-400 hover:shadow-sm transition-all group"
          @click="openRoom(room)"
        >
          <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
            {{ room.number }}
          </span>
        </button>
      </div>
    </div>
  </div>

  <PublicScheduleModal
    v-if="selectedRoom && selectedBuilding"
    v-model:open="modalOpen"
    :entity-id="selectedRoom.id"
    :entity-name="`Аудитория ${selectedRoom.number}, ${selectedBuilding.name}`"
    entity-type="classroom"
  />
</template>
