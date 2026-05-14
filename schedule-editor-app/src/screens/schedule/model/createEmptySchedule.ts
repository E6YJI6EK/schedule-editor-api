import type { Schedule, DaySchedule, TimeSlot as ScheduleTimeSlot } from '@/types/schedule';
import type { DayPartition, Group } from '@/api/types';
import { formatPartitionLabel } from './dayPartitionsStore';

const DAYS = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

export function createEmptySchedule(groups: Group[], dayPartitions: DayPartition[]): Schedule {
  const groupNames = groups.map(g => g.name);

  const createEmptyWeek = (): DaySchedule[] =>
    DAYS.map(day => {
      const timeslots: ScheduleTimeSlot[] = dayPartitions.map(partition => ({
        time: formatPartitionLabel(partition),
        groups: groups.map(group => ({
          subject: null,
          teacher: null,
          room: null,
          building: null,
          groupId: group.id,
        })),
      }));

      return { day, timeslots };
    });

  return {
    groups: groupNames,
    upperWeek: createEmptyWeek(),
    lowerWeek: createEmptyWeek(),
  };
}
