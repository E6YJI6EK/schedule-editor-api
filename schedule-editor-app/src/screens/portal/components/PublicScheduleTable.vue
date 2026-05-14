<script setup lang="ts">
import type { LessonWithRelations } from '@/api/lessons'

type ViewMode = 'group' | 'teacher' | 'classroom'

interface Props {
  lessons: LessonWithRelations[]
  mode: ViewMode
}

const props = defineProps<Props>()

const DAYS = [
  { num: 1, label: 'Понедельник' },
  { num: 2, label: 'Вторник' },
  { num: 3, label: 'Среда' },
  { num: 4, label: 'Четверг' },
  { num: 5, label: 'Пятница' },
  { num: 6, label: 'Суббота' },
]

const TIME_SLOTS = [
  { id: 1, time: '08:30–10:00' },
  { id: 2, time: '10:10–11:40' },
  { id: 3, time: '12:00–13:30' },
  { id: 4, time: '13:40–15:10' },
  { id: 5, time: '15:20–16:50' },
  { id: 6, time: '17:00–18:30' },
]

function getLesson(dayNum: number, partitionId: number): LessonWithRelations | undefined {
  return props.lessons.find(
    (l) => l.time_slot.day === dayNum && l.time_slot.day_partition?.id === partitionId
  )
}
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full border-collapse text-sm">
      <thead>
        <tr class="bg-gray-100">
          <th class="px-3 py-2 text-left font-medium text-gray-600 border border-gray-200 w-28">
            Время
          </th>
          <th
            v-for="day in DAYS"
            :key="day.num"
            class="px-3 py-2 text-center font-medium text-gray-600 border border-gray-200"
          >
            {{ day.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="slot in TIME_SLOTS" :key="slot.id" class="hover:bg-gray-50">
          <td class="px-3 py-2 text-center text-gray-500 font-medium border border-gray-200 bg-gray-50 whitespace-nowrap">
            {{ slot.time }}
          </td>
          <td
            v-for="day in DAYS"
            :key="day.num"
            class="px-3 py-2 border border-gray-200 align-top min-w-[140px]"
          >
            <template v-if="getLesson(day.num, slot.id)">
              <div class="space-y-0.5">
                <div class="font-medium text-gray-800 leading-tight">
                  {{ getLesson(day.num, slot.id)!.discipline?.name }}
                </div>
                <div v-if="mode !== 'teacher'" class="text-xs text-gray-500">
                  {{ getLesson(day.num, slot.id)!.teacher?.name }}
                </div>
                <div v-if="mode !== 'group'" class="text-xs text-blue-600 font-medium">
                  {{ getLesson(day.num, slot.id)!.group?.name }}
                </div>
                <div v-if="mode !== 'classroom'" class="text-xs text-gray-400">
                  {{ getLesson(day.num, slot.id)!.class_room?.number }}
                  {{ getLesson(day.num, slot.id)!.class_room?.building?.short_name }}
                </div>
              </div>
            </template>
            <template v-else>
              <span class="text-gray-300">—</span>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
