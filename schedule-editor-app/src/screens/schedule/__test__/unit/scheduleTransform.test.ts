import { describe, it, expect } from 'vitest'
import {
  transformLessonsToWeekSchedule,
  getGroupsFromLessons,
} from '../../model/scheduleTransform'
import type { LessonWithRelations } from '@/api/lessons'
import { EducationForm } from '@/api/types'

function makeLesson(overrides: Partial<LessonWithRelations> = {}): LessonWithRelations {
  return {
    id: 1,
    teacher: { id: 1, name: 'Иванов И.И.' },
    class_room: {
      id: 1,
      number: '101',
      building: { id: 1, short_name: 'А' },
    },
    time_slot: {
      id: 1,
      week_type: 'upper' as any,
      day: 1, // Понедельник
      day_partition: { id: 1, start_time: '08:30', end_time: '10:00' },
    },
    discipline: { id: 1, name: 'Математика' },
    group: {
      id: 1,
      name: 'ИТ-11',
      course: 1,
      education_form: EducationForm.FullTime,
      institute_id: 1,
    },
    ...overrides,
  }
}

// ─── getGroupsFromLessons ────────────────────────────────────────────────────

describe('getGroupsFromLessons', () => {
  it('returns group names for requested ids', () => {
    const result = getGroupsFromLessons([makeLesson()], [1])
    expect(result).toEqual(['ИТ-11'])
  })

  it('returns empty array when no lessons match groupIds', () => {
    const result = getGroupsFromLessons([makeLesson()], [99])
    expect(result).toEqual([])
  })

  it('deduplicates groups with the same id across multiple lessons', () => {
    const lessons = [makeLesson({ id: 1 }), makeLesson({ id: 2 })]
    const result = getGroupsFromLessons(lessons, [1])
    expect(result).toEqual(['ИТ-11'])
  })

  it('preserves groupIds ordering in output', () => {
    const lesson1 = makeLesson({ id: 1, group: { id: 1, name: 'A', course: 1, education_form: EducationForm.FullTime, institute_id: 1 } })
    const lesson2 = makeLesson({ id: 2, group: { id: 2, name: 'B', course: 1, education_form: EducationForm.FullTime, institute_id: 1 } })
    const result = getGroupsFromLessons([lesson1, lesson2], [2, 1])
    expect(result).toEqual(['B', 'A'])
  })

  it('returns empty array for empty lessons', () => {
    expect(getGroupsFromLessons([], [1, 2])).toEqual([])
  })

  it('filters out groups not in requested ids', () => {
    const lesson1 = makeLesson({ id: 1, group: { id: 1, name: 'A', course: 1, education_form: EducationForm.FullTime, institute_id: 1 } })
    const lesson2 = makeLesson({ id: 2, group: { id: 2, name: 'B', course: 1, education_form: EducationForm.FullTime, institute_id: 1 } })
    const result = getGroupsFromLessons([lesson1, lesson2], [1])
    expect(result).toEqual(['A'])
  })
})

// ─── transformLessonsToWeekSchedule ─────────────────────────────────────────

describe('transformLessonsToWeekSchedule', () => {
  it('always produces exactly 6 days', () => {
    const result = transformLessonsToWeekSchedule([], [1])
    expect(result).toHaveLength(6)
  })

  it('each day has exactly 6 timeslots', () => {
    const result = transformLessonsToWeekSchedule([], [1])
    result.forEach(day => expect(day.timeslots).toHaveLength(6))
  })

  it('maps day names to Russian weekday names in correct order', () => {
    const result = transformLessonsToWeekSchedule([], [])
    expect(result.map(d => d.day)).toEqual([
      'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота',
    ])
  })

  it('first timeslot has time 08:30–10:00', () => {
    const result = transformLessonsToWeekSchedule([], [1])
    expect(result[0].timeslots[0].time).toBe('08:30–10:00')
  })

  it('last timeslot has time 17:00–18:30', () => {
    const result = transformLessonsToWeekSchedule([], [1])
    expect(result[0].timeslots[5].time).toBe('17:00–18:30')
  })

  it('places lesson in Monday first slot for day=1, day_partition.id=1', () => {
    const result = transformLessonsToWeekSchedule([makeLesson()], [1])
    const cell = result[0].timeslots[0].groups[0]
    expect(cell.subject?.name).toBe('Математика')
    expect(cell.teacher?.name).toBe('Иванов И.И.')
    expect(cell.room?.number).toBe('101')
    expect(cell.building?.name).toBe('А')
    expect(cell.lessonId).toBe(1)
  })

  it('creates null-filled cell for group without lesson in that slot', () => {
    const result = transformLessonsToWeekSchedule([], [1])
    const cell = result[0].timeslots[0].groups[0]
    expect(cell.subject).toBeNull()
    expect(cell.teacher).toBeNull()
    expect(cell.room).toBeNull()
    expect(cell.building).toBeNull()
  })

  it('assigns groupId to each cell matching requested group', () => {
    const result = transformLessonsToWeekSchedule([], [42])
    const cell = result[0].timeslots[0].groups[0]
    expect(cell.groupId).toBe(42)
  })

  it('handles lesson without day_partition without throwing', () => {
    const lesson = makeLesson({
      time_slot: { id: 1, week_type: 'upper' as any, day: 1, day_partition: null as any },
    })
    expect(() => transformLessonsToWeekSchedule([lesson], [1])).not.toThrow()
  })

  it('lesson appears only for the correct group, sibling group stays empty', () => {
    const result = transformLessonsToWeekSchedule([makeLesson()], [1, 2])
    const slot = result[0].timeslots[0]
    expect(slot.groups[0].subject?.name).toBe('Математика')
    expect(slot.groups[1].subject).toBeNull()
  })

  it('places lesson in correct day slot based on day number', () => {
    // day=3 → Среда (index 2), day_partition.id=2 → timeslot index 1
    const lesson = makeLesson({
      time_slot: { id: 5, week_type: 'upper' as any, day: 3, day_partition: { id: 2, start_time: '10:10', end_time: '11:40' } },
    })
    const result = transformLessonsToWeekSchedule([lesson], [1])
    expect(result[2].timeslots[1].groups[0].subject?.name).toBe('Математика')
    // other slots are empty
    expect(result[0].timeslots[0].groups[0].subject).toBeNull()
  })

  it('handles multiple lessons in different slots', () => {
    const lesson1 = makeLesson({ id: 1 }) // Mon, slot 1
    const lesson2 = makeLesson({
      id: 2,
      discipline: { id: 2, name: 'Физика' },
      time_slot: { id: 2, week_type: 'upper' as any, day: 2, day_partition: { id: 1, start_time: '08:30', end_time: '10:00' } },
    })
    const result = transformLessonsToWeekSchedule([lesson1, lesson2], [1])
    expect(result[0].timeslots[0].groups[0].subject?.name).toBe('Математика')
    expect(result[1].timeslots[0].groups[0].subject?.name).toBe('Физика')
  })
})
