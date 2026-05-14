<script setup lang="ts">
import { useToast } from "@/core/ui/toast/model/hooks/useToast";
import BuildingSelect from "@/units/building-select/view.vue";
import RoomSelect from "@/units/room-select/view.vue";
import SubjectSelect from "@/units/subject-select/view.vue";
import TeacherSelect from "@/units/teacher-select/view.vue";
import { reactive, ref } from "vue";

import type { ClassData, WeekType } from "@/types/schedule";

const toast = useToast();

interface Props {
  cellData: ClassData;
  day: string;
  time: string;
  group: string;
  groupIndex: number;
  weekType: WeekType;
  color?: string;
  updateCell(week: WeekType, day: string, time: string, groupIndex: number, newData: Partial<ClassData>): Promise<void>;
  moveCell(fromWeek: WeekType, fromDay: string, fromTime: string, fromGroupIndex: number, toWeek: WeekType, toDay: string, toTime: string, toGroupIndex: number): Promise<void>;
}

const props = withDefaults(defineProps<Props>(), {
  color: "#ffffff",
});

const showEditDialog = ref(false);
const isDragging = ref(false);
const isDragOver = ref(false);
const isBeingDragged = ref(false);

const editData = reactive<ClassData>({
  subject: null,
  teacher: null,
  room: null,
  building: null,
});

const openEditDialog = () => {
  if (isBeingDragged.value) {
    isBeingDragged.value = false;
    return;
  }
  Object.assign(editData, props.cellData);
  showEditDialog.value = true;
};

const closeEditDialog = () => {
  showEditDialog.value = false;
};

const saving = ref(false);
const saveError = ref<string | null>(null);

const saveChanges = async () => {
  saving.value = true;
  saveError.value = null;

  try {
    await props.updateCell(
      props.weekType,
      props.day,
      props.time,
      props.groupIndex,
      {
        ...editData,
        groupId: props.cellData.groupId,
      }
    );
    closeEditDialog();
  } catch (error: any) {
    saveError.value = error.message || 'Ошибка при сохранении';
  } finally {
    saving.value = false;
  }
};

const handleDragStart = (event: DragEvent) => {
  if (!props.cellData.subject || !event.dataTransfer) return;
  isDragging.value = true;
  isBeingDragged.value = true;
  const dragData = {
    weekType: props.weekType,
    day: props.day,
    time: props.time,
    groupIndex: props.groupIndex,
    cellData: { ...props.cellData },
  };
  event.dataTransfer.effectAllowed = "move";
  event.dataTransfer.setData("application/json", JSON.stringify(dragData));
};

const handleDragOver = (event: DragEvent) => {
  if (!event.dataTransfer) return;
  event.preventDefault();
  isDragOver.value = true;
  event.dataTransfer.dropEffect = "move";
};

const handleDragEnd = () => {
  isDragging.value = false;
  setTimeout(() => {
    isBeingDragged.value = false;
  }, 100);
};

const handleDragLeave = () => {
  isDragOver.value = false;
};

const isMoving = ref(false);

const handleDrop = async (event: DragEvent) => {
  if (!event.dataTransfer) return;
  event.preventDefault();
  isDragOver.value = false;
  isDragging.value = false;
  try {
    const dragData = JSON.parse(event.dataTransfer.getData("application/json"));
    if (
      dragData.weekType === props.weekType &&
      dragData.day === props.day &&
      dragData.time === props.time &&
      dragData.groupIndex === props.groupIndex
    ) {
      return;
    }
    isMoving.value = true;
    await props.moveCell(
      dragData.weekType,
      dragData.day,
      dragData.time,
      dragData.groupIndex,
      props.weekType,
      props.day,
      props.time,
      props.groupIndex
    );
    toast.success('Пара успешно перемещена');
  } catch (error: any) {
    toast.error(error?.message || 'Не удалось переместить пару');
  } finally {
    isMoving.value = false;
  }
};
</script>

<template>
  <div :draggable="!!cellData.subject && !isMoving" @dragstart="handleDragStart" @dragend="handleDragEnd"
    @dragover.prevent="handleDragOver" @dragleave="handleDragLeave" @drop.prevent="handleDrop" @click="openEditDialog"
    class="h-20 p-2 border border-gray-300 rounded-lg transition-all relative" :class="{
      'cursor-move hover:shadow-md': cellData.subject && !isMoving,
      'cursor-pointer hover:shadow-md': !cellData.subject && !isMoving,
      'bg-blue-100 border-blue-400 border-2': isDragOver,
      'opacity-50': isDragging,
      'cursor-wait': isMoving,
      'animate-pulse': isMoving,
    }" :style="{ backgroundColor: isDragOver ? '#dbeafe' : color }">
    <div v-if="cellData.subject" class="text-xs pointer-events-none relative">
      <div class="font-semibold text-gray-800 truncate">
        {{ cellData.subject.name }}
      </div>
      <div class="text-gray-600 truncate">{{ cellData.teacher?.name }}</div>
      <div class="text-gray-600 truncate">
        {{ cellData.room?.number }} ({{ cellData.building?.name }})
      </div>
      <div class="absolute top-0 right-0 text-gray-400 text-xs opacity-50">
        ⋮⋮
      </div>
    </div>
    <div v-else class="flex items-center justify-center h-full text-gray-400 text-sm pointer-events-none">
      Пусто
    </div>
  </div>

  <div v-if="showEditDialog" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
    @click="closeEditDialog">
    <div class="bg-white rounded-lg p-6 w-96 max-w-full mx-4" @click.stop>
      <h3 class="text-lg font-semibold mb-4">
        Редактировать пару - {{ group }}
      </h3>

      <div class="space-y-4">
        <SubjectSelect v-model="editData.subject" />
        <TeacherSelect :discipline-id="editData.subject?.id" v-model="editData.teacher" />
        <BuildingSelect v-model="editData.building" />
        <RoomSelect :building-id="editData.building?.id" v-model="editData.room" />
      </div>

      <div v-if="saveError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md text-red-700 text-sm">
        {{ saveError }}
      </div>

      <div class="flex justify-end space-x-3 mt-6">
        <button @click="closeEditDialog" :disabled="saving"
          class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          Отмена
        </button>
        <button @click="saveChanges" :disabled="saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
          <span v-if="saving" class="mr-2">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
          </span>
          {{ saving ? 'Сохранение...' : 'Сохранить' }}
        </button>
      </div>
    </div>
  </div>
</template>
