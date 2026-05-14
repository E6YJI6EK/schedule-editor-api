import { Day } from "@/api/types";

const dayNameToDayEnum: Record<string, Day> = {
  "Понедельник": Day.Mon,
  "Вторник": Day.Tue,
  "Среда": Day.Wed,
  "Четверг": Day.Thu,
  "Пятница": Day.Fri,
  "Суббота": Day.Sat,
  "Воскресенье": Day.Sun,
};

const dayEnumToNumber: Record<Day, number> = {
  [Day.Mon]: 1,
  [Day.Tue]: 2,
  [Day.Wed]: 3,
  [Day.Thu]: 4,
  [Day.Fri]: 5,
  [Day.Sat]: 6,
  [Day.Sun]: 7,
};

export function getDayNumber(dayName: string): number {
  const dayEnum = dayNameToDayEnum[dayName];
  return dayEnum ? dayEnumToNumber[dayEnum] : 1;
}
