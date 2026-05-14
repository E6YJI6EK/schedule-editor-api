import { defineStore } from "pinia";
import { mockSchedule } from "@/screens/schedule/model/mockData";
import type { Schedule, WeekType, WeekTypeShort, ClassData } from "@/types/schedule";
import { getSchedule, createLesson, updateLesson, getTimeSlotId } from "@/api/lessons";
import {
  transformLessonsToWeekSchedule,
  getGroupsFromLessons,
} from "@/screens/schedule/model/scheduleTransform";
import { createEmptySchedule } from "./createEmptySchedule";
import type { Group } from "@/api/types";
import { getDayNumber } from "./timeSlotMapping";
import { useDayPartitionsStore } from "./dayPartitionsStore";

interface ScheduleState {
  schedule: Schedule;
  currentWeek: WeekTypeShort;
  loading: boolean;
  error: string | null;
}

export const useScheduleStore = defineStore("schedule", {
  state: (): ScheduleState => ({
    schedule: mockSchedule,
    currentWeek: "upper" as WeekTypeShort,
    loading: false,
    error: null,
  }),
  actions: {
    switchWeek(): void {
      this.currentWeek = this.currentWeek === "upper" ? "lower" : "upper";
    },

    async updateCell(
      week: WeekType,
      day: string,
      time: string,
      groupIndex: number,
      newData: Partial<ClassData>
    ): Promise<void> {
      const targetWeek = this.schedule[week];
      const dayData = targetWeek.find((d) => d.day === day);
      const timeSlot = dayData?.timeslots.find((t) => t.time === time);

      if (!timeSlot || !timeSlot.groups || !timeSlot.groups[groupIndex]) {
        throw new Error("Не удалось найти ячейку расписания");
      }

      const currentCell = timeSlot.groups[groupIndex];
      const isUpperWeek = week === "upperWeek";

      if (!newData.subject || !newData.teacher || !newData.room || !newData.groupId) {
        Object.assign(currentCell, newData);
        return;
      }

      const dayPartitionsStore = useDayPartitionsStore();

      try {
        if (currentCell.lessonId) {
          await updateLesson(currentCell.lessonId, {
            discipline_id: newData.subject.id,
            teacher_id: newData.teacher.id,
            class_room_id: newData.room.id,
          });
          Object.assign(currentCell, newData);
        } else {
          const dayNumber = getDayNumber(day);
          const dayPartitionId = dayPartitionsStore.getPartitionIdByLabel(time);
          const weekType = isUpperWeek ? "upper" : "lower";

          const timeSlotResponse = await getTimeSlotId({
            week_type: weekType,
            day: dayNumber,
            day_partition_id: dayPartitionId,
          });

          const timeSlotId = timeSlotResponse.data.id;

          const response = await createLesson({
            discipline_id: newData.subject.id,
            teacher_id: newData.teacher.id,
            class_room_id: newData.room.id,
            group_id: newData.groupId,
            time_slot_id: timeSlotId,
          });

          Object.assign(currentCell, {
            ...newData,
            lessonId: response.data.id,
            timeSlotId,
          });
        }
      } catch (error: any) {
        console.error("Ошибка при сохранении урока:", error);
        throw new Error(
          error?.response?.data?.message || "Не удалось сохранить изменения"
        );
      }
    },

    addNewClass(
      week: WeekType,
      day: string,
      time: string,
      groupIndex: number,
      classData: ClassData
    ): void {
      const targetWeek = this.schedule[week];
      const dayData = targetWeek.find((d) => d.day === day);
      const timeSlot = dayData?.timeslots.find((t) => t.time === time);
      if (timeSlot && timeSlot.groups && timeSlot.groups[groupIndex]) {
        Object.assign(timeSlot.groups[groupIndex], classData);
      }
    },

    async moveCell(
      fromWeek: WeekType,
      fromDay: string,
      fromTime: string,
      fromGroupIndex: number,
      toWeek: WeekType,
      toDay: string,
      toTime: string,
      toGroupIndex: number
    ): Promise<void> {
      const fromWeekData = this.schedule[fromWeek];
      const fromDayData = fromWeekData.find((d) => d.day === fromDay);
      const fromTimeSlot = fromDayData?.timeslots.find((t) => t.time === fromTime);
      const fromCell = fromTimeSlot?.groups?.[fromGroupIndex];
      if (!fromCell) return;

      const toWeekData = this.schedule[toWeek];
      const toDayData = toWeekData.find((d) => d.day === toDay);
      const toTimeSlot = toDayData?.timeslots.find((t) => t.time === toTime);
      const toCell = toTimeSlot?.groups?.[toGroupIndex];
      if (!toCell) return;

      const originalFromCell = { ...fromCell };
      const originalToCell = { ...toCell };

      const temp = { ...fromCell };
      Object.assign(fromCell, toCell);
      Object.assign(toCell, temp);

      try {
        const dayPartitionsStore = useDayPartitionsStore();

        const fromDayNumber = getDayNumber(fromDay);
        const fromDayPartitionId = dayPartitionsStore.getPartitionIdByLabel(fromTime);
        const fromWeekType = toWeek === "upperWeek" ? "upper" : "lower";

        const toDayNumber = getDayNumber(toDay);
        const toDayPartitionId = dayPartitionsStore.getPartitionIdByLabel(toTime);
        const toWeekType = fromWeek === "upperWeek" ? "upper" : "lower";

        const updates: Promise<any>[] = [];
        console.log(toDayNumber, toDayPartitionId, fromDayNumber, fromDayPartitionId);

        const newToTimeSlotResponse = await getTimeSlotId({
          week_type: toWeekType,
          day: toDayNumber,
          day_partition_id: toDayPartitionId,
        });
        const newFromTimeSlotResponse = await getTimeSlotId({
          week_type: fromWeekType,
          day: fromDayNumber,
          day_partition_id: fromDayPartitionId,
        });

        if (originalFromCell.lessonId && originalFromCell.subject) {
          updates.push(
            updateLesson(originalFromCell.lessonId, {
              time_slot_id: newToTimeSlotResponse.data.id,
            }).then(() => {
              toCell.timeSlotId = newToTimeSlotResponse.data.id;
            })
          );
        }

        if (originalToCell.lessonId && originalToCell.subject) {
          updates.push(
            updateLesson(originalToCell.lessonId, {
              time_slot_id: newFromTimeSlotResponse.data.id,
            }).then(() => {
              fromCell.timeSlotId = newFromTimeSlotResponse.data.id;
            })
          );
        }

        await Promise.all(updates);
      } catch (error: any) {
        console.error("Ошибка при перемещении урока:", error);
        Object.assign(fromCell, originalFromCell);
        Object.assign(toCell, originalToCell);
        this.error = error?.response?.data?.message || "Не удалось переместить урок";
        throw error;
      }
    },

    getCurrentWeekData() {
      const weekType: WeekType =
        this.currentWeek === "upper" ? "upperWeek" : "lowerWeek";
      return this.schedule[weekType];
    },

    getWeekData(weekType: WeekType) {
      return this.schedule[weekType];
    },

    async loadSchedule(groupIds: number[], isUpperWeek: boolean, groups: Group[]) {
      const dayPartitionsStore = useDayPartitionsStore();

      this.loading = true;
      this.error = null;
      try {
        const response = await getSchedule({ group_ids: groupIds, is_upper_week: isUpperWeek });

        if (response.success && response.data) {
          const weekSchedule = transformLessonsToWeekSchedule(
            response.data,
            groupIds,
            dayPartitionsStore.partitions
          );

          if (isUpperWeek) {
            this.schedule.upperWeek = weekSchedule;
          } else {
            this.schedule.lowerWeek = weekSchedule;
          }

          const groupNames = getGroupsFromLessons(response.data, groupIds);
          if (groupNames.length > 0) {
            this.schedule.groups = groupNames;
          }
        }
      } catch (error: any) {
        if (error?.status === 404 || error?.response?.status === 404) {
          const emptySchedule = createEmptySchedule(groups, dayPartitionsStore.partitions);
          if (isUpperWeek) {
            this.schedule.upperWeek = emptySchedule.upperWeek;
          } else {
            this.schedule.lowerWeek = emptySchedule.lowerWeek;
          }
          this.schedule.groups = emptySchedule.groups;
          this.error = null;
        } else {
          this.error = error?.message || "Ошибка при загрузке расписания";
          console.error("Error loading schedule:", error);
        }
      } finally {
        this.loading = false;
      }
    },

    async loadBothWeeks(groupIds: number[], groups: Group[]) {
      if (groups.length === 0) return;

      const dayPartitionsStore = useDayPartitionsStore();
      await dayPartitionsStore.load();

      const emptySchedule = createEmptySchedule(groups, dayPartitionsStore.partitions);
      this.schedule.groups = emptySchedule.groups;
      this.schedule.upperWeek = emptySchedule.upperWeek;
      this.schedule.lowerWeek = emptySchedule.lowerWeek;

      await Promise.all([
        this.loadSchedule(groupIds, true, groups),
        this.loadSchedule(groupIds, false, groups),
      ]);
    },
  },
});
