<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Pencil, X, Clock } from 'lucide-vue-next'
import * as dayPartitionsApi from '@/api/dayPartitions'
import type { DayPartition } from '@/api/types'
import { useToast } from '@/core/ui/toast/model/hooks/useToast'
import { usePagination } from '@/core/lib/usePagination'
import Pagination from '@/core/ui/pagination/Pagination.vue'

const toast = useToast()

const partitions = ref<DayPartition[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmDeleteId = ref<number | null>(null)
const deleting = ref(false)

const editingId = ref<number | null>(null)
const form = ref({ start_time: '', end_time: '' })

const { page, perPage, paginatedItems, resetPage } = usePagination(partitions)

const formatTime = (t: string) => t.slice(0, 5)

const load = async () => {
  loading.value = true
  try {
    const res = await dayPartitionsApi.listDayPartitions()
    partitions.value = res.data
  } catch (err: any) {
    toast.error(err?.message || 'Ошибка загрузки')
  } finally {
    loading.value = false
  }
}

const startEdit = (p: DayPartition) => {
  editingId.value = p.id
  form.value.start_time = formatTime(p.start_time)
  form.value.end_time = formatTime(p.end_time)
}

const cancelEdit = () => {
  editingId.value = null
  form.value = { start_time: '', end_time: '' }
}

const handleSubmit = async () => {
  if (!form.value.start_time || !form.value.end_time) return
  submitting.value = true
  const payload = { start_time: form.value.start_time, end_time: form.value.end_time }
  try {
    if (editingId.value !== null) {
      const res = await dayPartitionsApi.updateDayPartition(editingId.value, payload)
      const idx = partitions.value.findIndex(p => p.id === editingId.value)
      if (idx !== -1) partitions.value[idx] = res.data
      toast.success('Время обновлено')
      cancelEdit()
    } else {
      const res = await dayPartitionsApi.createDayPartition(payload)
      partitions.value.push(res.data)
      partitions.value.sort((a, b) => a.start_time.localeCompare(b.start_time))
      form.value = { start_time: '', end_time: '' }
      toast.success('Временной промежуток добавлен')
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
    await dayPartitionsApi.deleteDayPartition(id)
    partitions.value = partitions.value.filter(p => p.id !== id)
    confirmDeleteId.value = null
    toast.success('Временной промежуток удалён')
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
        {{ editingId !== null ? 'Редактировать время пары' : 'Добавить время пары' }}
      </h3>
      <form @submit.prevent="handleSubmit" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Начало</label>
          <input
            v-model="form.start_time"
            type="time"
            required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Конец</label>
          <input
            v-model="form.end_time"
            type="time"
            required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div class="flex items-end gap-2">
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

    <p v-else-if="partitions.length === 0" class="text-center py-8 text-gray-400 text-sm">
      Временные промежутки не добавлены
    </p>

    <template v-else>
      <div class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
        <div
          v-for="(partition, index) in paginatedItems"
          :key="partition.id"
          class="flex items-center justify-between px-4 py-3 bg-white"
          :class="{ 'bg-blue-50': editingId === partition.id }"
        >
          <div class="flex items-center gap-3">
            <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold flex items-center justify-center">
              {{ (page - 1) * perPage + index + 1 }}
            </span>
            <Clock class="w-4 h-4 text-gray-400" />
            <span class="text-sm font-medium text-gray-800">
              {{ formatTime(partition.start_time) }} — {{ formatTime(partition.end_time) }}
            </span>
          </div>

          <div class="flex items-center gap-2">
            <template v-if="confirmDeleteId === partition.id">
              <span class="text-xs text-gray-600">Удалить?</span>
              <button
                @click="handleDelete(partition.id)"
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
                @click="startEdit(partition)"
                class="px-3 py-1 bg-gray-50 text-gray-600 text-xs rounded-md hover:bg-gray-100 border border-gray-200 transition-colors flex items-center gap-1"
              >
                <Pencil class="w-3 h-3" /> Изменить
              </button>
              <button
                @click="confirmDeleteId = partition.id"
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
        :total="partitions.length"
        :per-page="perPage"
        @update:page="page = $event"
      />
    </template>
  </div>
</template>
