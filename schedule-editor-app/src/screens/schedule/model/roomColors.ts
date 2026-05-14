import type { Schedule, RoomColors } from '@/types/schedule';

const COLOR_PALETTE = [
  '#b3e5fc', // голубой
  '#c8e6c9', // зелёный
  '#fff9c4', // жёлтый
  '#ffe0b2', // оранжевый
  '#f8bbd0', // розовый
  '#e1bee7', // фиолетовый
  '#b2ebf2', // циановый
  '#dcedc8', // лаймовый
  '#ffe082', // янтарный
  '#ffccbc', // персиковый
  '#d7ccc8', // коричневый
  '#cfd8dc', // сине-серый
  '#f0f4c3', // лимонный
  '#fce4ec', // малиновый
  '#e8eaf6', // индиговый
  '#e0f2f1', // бирюзовый
  '#fff3e0', // кремовый
  '#e3f2fd', // небесный
  '#f3e5f5', // лавандовый
  '#e8f5e9', // мятный
];

export function buildRoomColors(schedule: Schedule): RoomColors {
  const rooms = new Set<string>();

  for (const week of [schedule.upperWeek, schedule.lowerWeek]) {
    for (const day of week) {
      for (const slot of day.timeslots) {
        if (!slot.groups) continue;
        for (const cell of slot.groups) {
          if (cell?.room?.number) rooms.add(cell.room.number);
        }
      }
    }
  }

  const colors: RoomColors = {};
  let i = 0;
  for (const room of rooms) {
    colors[room] = COLOR_PALETTE[i % COLOR_PALETTE.length];
    i++;
  }
  return colors;
}
