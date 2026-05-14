<script setup lang="ts">
import { computed } from "vue";
import { Button } from "@/core/ui/button";
import Toolbar from "@/blocks/schedule-toolbar/view/Toolbar.vue";
import ScheduleTable from "@/blocks/schedule-table/view.vue";
import GroupMultiSelect from "@/units/group-multi-select/view.vue";
import { useScheduleScreen } from "./model/useScheduleScreen";
import { buildRoomColors } from "./model/roomColors";

const {
  scheduleStore,
  selectedGroups,
  selectedGroupIds,
  showSchedule,
  isLoading,
  error,
  handleShowSchedule,
} = useScheduleScreen();

const roomColors = computed(() => buildRoomColors(scheduleStore.schedule));
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <Toolbar
      :scheduleShown="showSchedule && !isLoading"
      :schedule="scheduleStore.schedule"
      :currentWeek="scheduleStore.currentWeek"
      :switchWeek="scheduleStore.switchWeek"
      :groupIds="selectedGroupIds"
    />

    <div class="container mx-auto px-4 py-6 space-y-8">
      <div v-if="!showSchedule" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Выбор групп для расписания</h2>
        
        <div class="space-y-4">
          <GroupMultiSelect v-model="selectedGroups" />
          
          <div class="flex justify-end">
            <Button
              @click="handleShowSchedule"
              :disabled="selectedGroups.length === 0 || isLoading"
              class="px-6 py-2"
            >
              Показать расписание
            </Button>
          </div>
        </div>
      </div>

      <div v-if="error && showSchedule" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ error }}
      </div>

      <div v-if="isLoading && showSchedule" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        <p class="mt-2 text-gray-600">Загрузка расписания...</p>
      </div>

      <div v-if="showSchedule && !isLoading">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-xl font-semibold">Расписание</h2>
          <Button
            @click="showSchedule = false"
            variant="outline"
            class="px-4 py-2"
          >
            Изменить группы
          </Button>
        </div>
        
        <ScheduleTable
          :week-type="
            scheduleStore.currentWeek === 'upper' ? 'upperWeek' : 'lowerWeek'
          "
          :data="
            scheduleStore.getWeekData(
              scheduleStore.currentWeek === 'upper' ? 'upperWeek' : 'lowerWeek'
            )
          "
          :colors="roomColors"
          :schedule-groups="scheduleStore.schedule.groups"
          :update-cell="scheduleStore.updateCell"
          :move-cell="scheduleStore.moveCell"
        />
      </div>
    </div>
  </div>
</template>
