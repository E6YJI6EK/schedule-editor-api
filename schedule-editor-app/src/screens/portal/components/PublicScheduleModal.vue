<script setup lang="ts">
import { ref, watch } from 'vue'
import { X } from 'lucide-vue-next'
import type { LessonWithRelations } from '@/api/lessons'
import { getSchedule, getScheduleByTeacher, getScheduleByClassroom } from '@/api/lessons'
import PublicScheduleTable from './PublicScheduleTable.vue'

type EntityType = 'group' | 'teacher' | 'classroom'

interface Props {
  entityId: number
  entityName: string
  entityType: EntityType
  open: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{ 'update:open': [value: boolean] }>()

const isUpperWeek = ref(true)
const lessons = ref<LessonWithRelations[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

async function fetchSchedule() {
  loading.value = true
  error.value = null
  try {
    let res
    if (props.entityType === 'group') {
      res = await getSchedule({ group_ids: [props.entityId], is_upper_week: isUpperWeek.value })
    } else if (props.entityType === 'teacher') {
      res = await getScheduleByTeacher({ teacher_id: props.entityId, is_upper_week: isUpperWeek.value })
    } else {
      res = await getScheduleByClassroom({ classroom_id: props.entityId, is_upper_week: isUpperWeek.value })
    }
    lessons.value = res.data
  } catch {
    error.value = 'Расписание не найдено'
    lessons.value = []
  } finally {
    loading.value = false
  }
}

watch(
  () => [props.open, props.entityId, isUpperWeek.value],
  ([open]) => {
    if (open) fetchSchedule()
  },
  { immediate: true }
)

function close() {
  emit('update:open', false)
}

function handleBackdropClick(e: MouseEvent) {
  if (e.target === e.currentTarget) close()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 overflow-y-auto py-6 px-4"
        @click="handleBackdropClick"
      >
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">Расписание</h2>
              <p class="text-sm text-gray-500">{{ entityName }}</p>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex rounded-lg overflow-hidden border border-gray-200">
                <button
                  :class="['px-4 py-1.5 text-sm font-medium transition-colors', isUpperWeek ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                  @click="isUpperWeek = true"
                >
                  Верхняя
                </button>
                <button
                  :class="['px-4 py-1.5 text-sm font-medium transition-colors', !isUpperWeek ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                  @click="isUpperWeek = false"
                >
                  Нижняя
                </button>
              </div>
              <button
                class="p-1.5 rounded-md hover:bg-gray-100 text-gray-500 transition-colors"
                @click="close"
              >
                <X :size="20" />
              </button>
            </div>
          </div>

          <div class="p-6">
            <div v-if="loading" class="flex justify-center py-12">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600" />
            </div>
            <div v-else-if="error" class="text-center py-12 text-gray-400">
              {{ error }}
            </div>
            <PublicScheduleTable
              v-else
              :lessons="lessons"
              :mode="entityType"
            />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
