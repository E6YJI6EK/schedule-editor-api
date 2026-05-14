import type { Discipline, Teacher, Building, ClassRoom } from '@/api/types';
import type { Schedule } from '@/types/schedule';


// Helper constants for mock data
const disciplines: Record<string, Discipline> = {
  'Математика': { id: 1, name: 'Математика' },
  'Физика': { id: 2, name: 'Физика' },
  'История': { id: 3, name: 'История' },
  'Английский': { id: 4, name: 'Английский' },
  'Информатика': { id: 5, name: 'Информатика' },
  'Химия': { id: 6, name: 'Химия' },
  'Экономика': { id: 7, name: 'Экономика' },
  'Программирование': { id: 8, name: 'Программирование' },
  'Философия': { id: 9, name: 'Философия' },
  'Базы данных': { id: 10, name: 'Базы данных' },
  'Физкультура': { id: 11, name: 'Физкультура' },
};

const teachers: Record<string, Teacher> = {
  'Иванов И.И.': { id: 1, name: 'Иванов И.И.', discipline_id: 1 },
  'Петров П.П.': { id: 2, name: 'Петров П.П.', discipline_id: 2 },
  'Смирнов С.С.': { id: 3, name: 'Смирнов С.С.', discipline_id: 3 },
  'Козлова М.М.': { id: 4, name: 'Козлова М.М.', discipline_id: 4 },
  'Сидорова А.А.': { id: 5, name: 'Сидорова А.А.', discipline_id: 5 },
  'Павлов К.К.': { id: 6, name: 'Павлов К.К.', discipline_id: 3 },
  'Морозов В.В.': { id: 7, name: 'Морозов В.В.', discipline_id: 6 },
  'Новикова Л.Л.': { id: 8, name: 'Новикова Л.Л.', discipline_id: 7 },
  'Соколов Н.Н.': { id: 9, name: 'Соколов Н.Н.', discipline_id: 9 },
  'Титов О.О.': { id: 10, name: 'Титов О.О.', discipline_id: 11 },
};

const buildings: Record<string, Building> = {
  '1': { id: 1, name: '1' },
  '2': { id: 2, name: '2' },
  '3': { id: 3, name: '3' },
  '4': { id: 4, name: '4' },
  '5': { id: 5, name: '5' },
};

const classRooms: Record<string, ClassRoom> = {
  '101': { id: 1, number: '101', building_id: 1 },
  '102': { id: 2, number: '102', building_id: 1 },
  '201': { id: 3, number: '201', building_id: 2 },
  '301': { id: 4, number: '301', building_id: 3 },
  '401': { id: 5, number: '401', building_id: 4 },
  'Спортзал': { id: 6, number: 'Спортзал', building_id: 5 },
};

// Helper function to create class data
function createClassData(
  subject: string,
  teacher: string,
  room: string,
  building: string
) {
  return {
    subject: subject ? disciplines[subject] || null : null,
    teacher: teacher ? teachers[teacher] || null : null,
    room: room ? classRooms[room] || null : null,
    building: building ? buildings[building] || null : null,
  };
}

export const mockSchedule: Schedule = {
  groups: ['МПИ-101', 'МПИ-102', 'МПИ-103'], // Список групп/направлений
  upperWeek: [
    {
      day: 'Понедельник',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('Математика', 'Иванов И.И.', '101', '1'),
            createClassData('Физика', 'Петров П.П.', '102', '1'),
            createClassData('История', 'Смирнов С.С.', '201', '2')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Физика', 'Петров П.П.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('Английский', 'Козлова М.М.', '301', '3')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Информатика', 'Сидорова А.А.', '201', '2'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('Информатика', 'Сидорова А.А.', '201', '2'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Вторник',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('История', 'Павлов К.К.', '301', '3'),
            createClassData('', '', '', ''),
            createClassData('Программирование', 'Иванов И.И.', '101', '1')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Английский', 'Козлова М.М.', '401', '4'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Среда',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Химия', 'Морозов В.В.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('Физика', 'Петров П.П.', '102', '1')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Экономика', 'Новикова Л.Л.', '201', '2'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Четверг',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('Программирование', 'Иванов И.И.', '101', '1'),
            createClassData('', '', '', ''),
            createClassData('Математика', 'Иванов И.И.', '101', '1')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Философия', 'Соколов Н.Н.', '301', '3'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Пятница',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Базы данных', 'Петров П.П.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('Информатика', 'Сидорова А.А.', '201', '2')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5'),
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5'),
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Суббота',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Математика', 'Иванов И.И.', '101', '1'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    }
  ],
  lowerWeek: [
    {
      day: 'Понедельник',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('История', 'Павлов К.К.', '301', '3'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Математика', 'Иванов И.И.', '101', '1'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Вторник',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Физика', 'Петров П.П.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Информатика', 'Сидорова А.А.', '201', '2'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Среда',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('Английский', 'Козлова М.М.', '401', '4'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Химия', 'Морозов В.В.', '102', '1'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Четверг',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('Программирование', 'Иванов И.И.', '101', '1'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('Философия', 'Соколов Н.Н.', '301', '3'),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Пятница',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('Базы данных', 'Петров П.П.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5'),
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5'),
            createClassData('Физкультура', 'Титов О.О.', 'Спортзал', '5')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    },
    {
      day: 'Суббота',
      timeslots: [
        { 
          time: '08:30–10:00', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '10:10–11:40', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '12:00–13:30', 
          groups: [
            createClassData('Физика', 'Петров П.П.', '102', '1'),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '13:40–15:10', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '15:20–16:50', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
        { 
          time: '17:00–18:30', 
          groups: [
            createClassData('', '', '', ''),
            createClassData('', '', '', ''),
            createClassData('', '', '', '')
          ]
        },
      ]
    }
  ]
};

