import type { LessonWithRelations } from "@/api/lessons";
import type { DayPartition } from "@/api/types";
import type { DaySchedule, ClassData } from "@/types/schedule";
import { Day } from "@/api/types";
import { formatPartitionLabel } from "./dayPartitionsStore";

const dayMap: Record<Day, string> = {
  [Day.Mon]: "Понедельник",
  [Day.Tue]: "Вторник",
  [Day.Wed]: "Среда",
  [Day.Thu]: "Четверг",
  [Day.Fri]: "Пятница",
  [Day.Sat]: "Суббота",
  [Day.Sun]: "Воскресенье",
};

const dayKeys: Day[] = [Day.Mon, Day.Tue, Day.Wed, Day.Thu, Day.Fri, Day.Sat];
const dayNumbers = [1, 2, 3, 4, 5, 6];

export function transformLessonsToWeekSchedule(
  lessons: LessonWithRelations[],
  groupIds: number[],
  dayPartitions: DayPartition[]
): DaySchedule[] {
  const lessonsByDayAndPartition = new Map<string, LessonWithRelations[]>();

  lessons.forEach((lesson) => {
    const day = lesson.time_slot.day;
    const dayPartitionId = lesson.time_slot.day_partition?.id;
    if (!dayPartitionId) return;
    const key = `${day}-${dayPartitionId}`;
    if (!lessonsByDayAndPartition.has(key)) {
      lessonsByDayAndPartition.set(key, []);
    }
    lessonsByDayAndPartition.get(key)!.push(lesson);
  });

  return dayKeys.map((dayKey, index) => {
    const dayNumber = dayNumbers[index];
    const daySchedule: DaySchedule = {
      day: dayMap[dayKey],
      timeslots: [],
    };

    for (const partition of dayPartitions) {
      const time = formatPartitionLabel(partition);
      const key = `${dayNumber}-${partition.id}`;
      const lessonsForSlot = lessonsByDayAndPartition.get(key) || [];

      const classDataArray: ClassData[] = groupIds.map((groupId) => {
        const lesson = lessonsForSlot.find((l) => l.group?.id === groupId);

        if (lesson) {
          return {
            lessonId: lesson.id,
            subject: lesson.discipline
              ? { id: lesson.discipline.id, name: lesson.discipline.name }
              : null,
            teacher: lesson.teacher
              ? { id: lesson.teacher.id, name: lesson.teacher.name, discipline_id: 0 }
              : null,
            room: lesson.class_room
              ? {
                  id: lesson.class_room.id,
                  number: lesson.class_room.number,
                  building_id: lesson.class_room.building?.id || 0,
                }
              : null,
            building: lesson.class_room?.building
              ? { id: lesson.class_room.building.id, name: lesson.class_room.building.short_name }
              : null,
            groupId,
            timeSlotId: lesson.time_slot.id,
          };
        }

        return {
          subject: null,
          teacher: null,
          room: null,
          building: null,
          groupId,
          timeSlotId: undefined,
        };
      });

      daySchedule.timeslots.push({ time, groups: classDataArray });
    }

    return daySchedule;
  });
}

export function getGroupsFromLessons(
  lessons: LessonWithRelations[],
  groupIds: number[]
): string[] {
  const groupsMap = new Map<number, string>();
  lessons.forEach((lesson) => {
    if (lesson.group && !groupsMap.has(lesson.group.id)) {
      groupsMap.set(lesson.group.id, lesson.group.name);
    }
  });

  return groupIds
    .map((id) => groupsMap.get(id))
    .filter((name): name is string => !!name);
}
