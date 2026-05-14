// Types for schedule data structure

import type {
  Building,
  ClassRoom,
  Discipline,
  Teacher,
} from "@/api/types";

export interface ClassData {
  lessonId?: number; // ID урока для обновления (если существует)
  subject: Discipline | null;
  teacher: Teacher | null;
  room: ClassRoom | null;
  building: Building | null;
  groupId?: number; // ID группы
  timeSlotId?: number; // ID временного слота
}

export interface TimeSlot {
  time: string;
  groups: ClassData[] | null;
}

export interface DaySchedule {
  day: string;
  timeslots: TimeSlot[];
}

export interface Schedule {
  groups: string[];
  upperWeek: DaySchedule[];
  lowerWeek: DaySchedule[];
}

export interface RoomColors {
  [key: string]: string;
}

export type WeekType = "upperWeek" | "lowerWeek";
export type WeekTypeShort = "upper" | "lower";
