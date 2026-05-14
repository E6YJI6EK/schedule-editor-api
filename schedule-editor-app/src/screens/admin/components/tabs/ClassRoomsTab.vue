<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Pencil, X } from 'lucide-vue-next'
import * as classRoomsApi from '@/api/classRooms'
import * as buildingsApi from '@/api/buildings'
import type { ClassRoom, Building } from '@/api/types'
import { useToast } from '@/core/ui/toast/model/hooks/useToast'
import { usePagination } from '@/core/lib/usePagination'
import Pagination from '@/core/ui/pagination/Pagination.vue'

const toast = useToast()

const classRooms = ref<ClassRoom[]>([])
const buildings = ref<Building[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmDeleteId = ref<number | null>(null)
const deleting = ref(false)

const editingId = ref<number | null>(null)
const form = ref({ number: '', building_id: '' })

const { page, perPage, paginatedItems, resetPage } = usePagination(classRooms)

const buildingName = (id: number) =>
  buildings.value.find(b => b.id === id)?.name ?? '—'

const load = async () => {
  loading.value = true
  try {
    const [roomsRes, buildingsRes] = await Promise.all([
      classRoomsApi.listClassRooms(),
      buildingsApi.listBuildings(),
    ])
    classRooms.value = roomsRes.data
    buildings.value = buildingsRes.data
  } catch (err: any) {
    toast.error(err?.message || 'Ошибка загрузки')
  } finally {
    loading.value = false
  }
}

const startEdit = (r: ClassRoom) => {
  editingId.value = r.id
  form.value.number = r.number
  form.value.building_id = String(r.building_id)
}

const cancelEdit = () => {
  editingId.value = null
  form.value = { number: '', building_id: '' }
}

const handleSubmit = async () => {
  if (!form.value.number.trim() || !form.value.building_id) return
  submitting.value = true
  const payload = { number: form.value.number, building_id: Number(form.value.building_id) }
  try {
    if (editingId.value !== null) {
      const res = await classRoomsApi.updateClassRoom(editingId.value, payload)
      const idx = classRooms.value.findIndex(r => r.id === editingId.value)
      if (idx !== -1) classRooms.value[idx] = res.data
      toast.success('Аудитория обновлена')
      cancelEdit()
    } else {
      const res = await classRoomsApi.createClassRoom(payload)
      classRooms.value.push(res.data)
      form.value = { number: '', building_id: '' }
      toast.success('Аудитория добавлена')
    }
  } catch (err: any) {
    toast.error(err?.message || 'Ошибка сохранения')
  } finally {
    submitting.value = false
  }
}

const handleDelete = async (id: number) => {
  deleting.value = true
  try {
    await classRoomsApi.deleteClassRoom(id)
    classRooms.value = classRooms.value.filter(r => r.id !== id)
    confirmDeleteId.value = null
    toast.success('Аудитория удалена')
    resetPage()
  } catch (err: any) {
    toast.error(err?.message || 'Ошибка удаления')
  } finally {
    deleting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-5">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">
        {{ editingId !== null ? 'Редактировать аудиторию' : 'Добавить аудиторию' }}
      </h3>
      <form @submit.prevent="handleSubmit" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input
          v-model="form.number"
          type="text"
          placeholder="Номер аудитории"
          required
          class="sm:col-span-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <select
          v-model="form.building_id"
          required
          class="sm:col-span-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
        >
          <option value="" disabled>Корпус</option>
          <option v-for="b in buildings" :key="b.id" :value="String(b.id)">{{ b.name }}</option>
        </select>
        <div class="flex gap-2">
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium whitespace-nowrap"
          >
            {{ submitting ? 'Сохранение...' : (editingId !== null ? 'Сохранить' : 'Добавить') }}
          </button>
          <button
            v-if="editingId !== null"
            type="button"
            @click="cancelEdit"
            class="px-3 py-2 bg-gray-100 text-gray-600 text-sm rounded-md hover:bg-gray-200 transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </form>
    </div>

    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900" />
    </div>

    <p v-else-if="classRooms.length === 0" class="text-center py-8 text-gray-400 text-sm">
      Аудитории не добавлены
    </p>

    <template v-else>
      <div class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
        <div
          v-for="room in paginatedItems"
          :key="room.id"
          class="flex items-center justify-between px-4 py-3 bg-white"
          :class="{ 'bg-blue-50': editingId === room.id }"
        >
          <div>
            <p class="text-sm font-medium text-gray-800">Аудитория {{ room.number }}</p>
            <p class="text-xs text-gray-500">{{ buildingName(room.building_id) }}</p>
          </div>

          <div class="flex items-center gap-2">
            <template v-if="confirmDeleteId === room.id">
              <span class="text-xs text-gray-600">Удалить?</span>
              <button
                @click="handleDelete(room.id)"
                :disabled="deleting"
                class="px-3 py-1 bg-red-600 text-white text-xs rounded-md hover:bg-red-700 transition-colors disabled:opacity-50"
              >
                Да
              </button>
              <button
                @click="confirmDeleteId = null"
                class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-md hover:bg-gray-200 transition-colors"
              >
                Нет
              </button>
            </template>
            <template v-else>
              <button
                @click="startEdit(room)"
                class="px-3 py-1 bg-gray-50 text-gray-600 text-xs rounded-md hover:bg-gray-100 border border-gray-200 transition-colors flex items-center gap-1"
              >
                <Pencil class="w-3 h-3" /> Изменить
              </button>
              <button
                @click="confirmDeleteId = room.id"
                class="px-3 py-1 bg-red-50 text-red-600 text-xs rounded-md hover:bg-red-100 border border-red-200 transition-colors"
              >
                Удалить
              </button>
            </template>
          </div>
        </div>
      </div>

      <Pagination
        :page="page"
        :total="classRooms.length"
        :per-page="perPage"
        @update:page="page = $event"
      />
    </template>
  </div>
</template>
