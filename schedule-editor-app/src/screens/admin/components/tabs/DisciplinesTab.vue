<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Pencil, X } from 'lucide-vue-next'
import * as disciplinesApi from '@/api/disciplines'
import type { Discipline } from '@/api/types'
import { useToast } from '@/core/ui/toast/model/hooks/useToast'
import { usePagination } from '@/core/lib/usePagination'
import Pagination from '@/core/ui/pagination/Pagination.vue'

const toast = useToast()

const disciplines = ref<Discipline[]>([])
const loading = ref(false)
const submitting = ref(false)
const confirmDeleteId = ref<number | null>(null)
const deleting = ref(false)

const editingId = ref<number | null>(null)
const form = ref({ name: '' })

const { page, perPage, paginatedItems, resetPage } = usePagination(disciplines)

const load = async () => {
  loading.value = true
  try {
    const res = await disciplinesApi.listDisciplines()
    disciplines.value = res.data
  } catch (err: any) {
    toast.error(err?.message || 'Ошибка загрузки')
  } finally {
    loading.value = false
  }
}

const startEdit = (d: Discipline) => {
  editingId.value = d.id
  form.value.name = d.name
}

const cancelEdit = () => {
  editingId.value = null
  form.value.name = ''
}

const handleSubmit = async () => {
  if (!form.value.name.trim()) return
  submitting.value = true
  try {
    if (editingId.value !== null) {
      const res = await disciplinesApi.updateDiscipline(editingId.value, { name: form.value.name })
      const idx = disciplines.value.findIndex(d => d.id === editingId.value)
      if (idx !== -1) disciplines.value[idx] = res.data
      toast.success('Дисциплина обновлена')
      cancelEdit()
    } else {
      const res = await disciplinesApi.createDiscipline({ name: form.value.name })
      disciplines.value.push(res.data)
      form.value.name = ''
      toast.success('Дисциплина добавлена')
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
    await disciplinesApi.deleteDiscipline(id)
    disciplines.value = disciplines.value.filter(d => d.id !== id)
    confirmDeleteId.value = null
    toast.success('Дисциплина удалена')
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
        {{ editingId !== null ? 'Редактировать дисциплину' : 'Добавить дисциплину' }}
      </h3>
      <form @submit.prevent="handleSubmit" class="flex gap-3">
        <input
          v-model="form.name"
          type="text"
          placeholder="Название дисциплины"
          required
          class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          type="submit"
          :disabled="submitting"
          class="px-5 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 font-medium whitespace-nowrap"
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
      </form>
    </div>

    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900" />
    </div>

    <p v-else-if="disciplines.length === 0" class="text-center py-8 text-gray-400 text-sm">
      Дисциплины не добавлены
    </p>

    <template v-else>
      <div class="divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
        <div
          v-for="discipline in paginatedItems"
          :key="discipline.id"
          class="flex items-center justify-between px-4 py-3 bg-white"
          :class="{ 'bg-blue-50': editingId === discipline.id }"
        >
          <span class="text-sm font-medium text-gray-800">{{ discipline.name }}</span>

          <div class="flex items-center gap-2">
            <template v-if="confirmDeleteId === discipline.id">
              <span class="text-xs text-gray-600">Удалить?</span>
              <button
                @click="handleDelete(discipline.id)"
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
                @click="startEdit(discipline)"
                class="px-3 py-1 bg-gray-50 text-gray-600 text-xs rounded-md hover:bg-gray-100 border border-gray-200 transition-colors flex items-center gap-1"
              >
                <Pencil class="w-3 h-3" /> Изменить
              </button>
              <button
                @click="confirmDeleteId = discipline.id"
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
        :total="disciplines.length"
        :per-page="perPage"
        @update:page="page = $event"
      />
    </template>
  </div>
</template>
