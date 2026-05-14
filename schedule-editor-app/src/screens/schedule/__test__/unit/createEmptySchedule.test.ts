import { describe, it, expect } from 'vitest'
import { createEmptySchedule } from '../../model/createEmptySchedule'
import type { Group } from '@/api/types'
import { Course, EducationForm } from '@/api/types'

function makeGroup(id: number, name: string): Group {
  return {
    id,
    name,
    course: Course.First,
    education_form: EducationForm.FullTime,
    institute_id: 1,
    discipline_id: 1,
  }
}

describe('createEmptySchedule', () => {
  it('returns an object with upperWeek and lowerWeek', () => {
    const result = createEmptySchedule([makeGroup(1, 'ИТ-11')])
    expect(result.upperWeek).toBeDefined()
    expect(result.lowerWeek).toBeDefined()
  })

  it('includes group names from input groups', () => {
    const result = createEmptySchedule([makeGroup(1, 'ИТ-11'), makeGroup(2, 'ИТ-12')])
    expect(result.groups).toEqual(['ИТ-11', 'ИТ-12'])
  })

  it('creates exactly 6 days per week', () => {
    const result = createEmptySchedule([makeGroup(1, 'ИТ-11')])
    expect(result.upperWeek).toHaveLength(6)
    expect(result.lowerWeek).toHaveLength(6)
  })

  it('creates exactly 6 timeslots per day', () => {
    const result = createEmptySchedule([makeGroup(1, 'ИТ-11')])
    result.upperWeek.forEach(day => expect(day.timeslots).toHaveLength(6))
    result.lowerWeek.forEach(day => expect(day.timeslots).toHaveLength(6))
  })

  it('all cells have null subject, teacher, room, and building', () => {
    const result = createEmptySchedule([makeGroup(1, 'ИТ-11')])
    result.upperWeek.forEach(day =>
      day.timeslots.forEach(slot =>
        slot.groups!.forEach(cell => {
          expect(cell.subject).toBeNull()
          expect(cell.teacher).toBeNull()
          expect(cell.room).toBeNull()
          expect(cell.building).toBeNull()
        })
      )
    )
  })

  it('each cell contains groupId from the corresponding group', () => {
    const result = createEmptySchedule([makeGroup(5, 'ИТ-11')])
    const cell = result.upperWeek[0]!.timeslots[0]!.groups![0]!
    expect(cell.groupId).toBe(5)
  })

  it('creates one cell per group per timeslot', () => {
    const result = createEmptySchedule([makeGroup(1, 'A'), makeGroup(2, 'B'), makeGroup(3, 'C')])
    const groups = result.upperWeek[0]!.timeslots[0]!.groups!
    expect(groups).toHaveLength(3)
  })

  it('returns empty groups array and empty cells when no groups given', () => {
    const result = createEmptySchedule([])
    expect(result.groups).toEqual([])
    result.upperWeek.forEach(day =>
      day.timeslots.forEach(slot => expect(slot.groups).toHaveLength(0))
    )
  })

  it('first timeslot time is 08:30–10:00', () => {
    const result = createEmptySchedule([makeGroup(1, 'A')])
    expect(result.upperWeek[0]!.timeslots[0]!.time).toBe('08:30–10:00')
  })

  it('last timeslot time is 17:00–18:30', () => {
    const result = createEmptySchedule([makeGroup(1, 'A')])
    expect(result.upperWeek[0]!.timeslots[5]!.time).toBe('17:00–18:30')
  })

  it('days include Понедельник through Суббота', () => {
    const result = createEmptySchedule([makeGroup(1, 'A')])
    const days = result.upperWeek.map(d => d.day)
    expect(days).toEqual(['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'])
  })

  it('upperWeek and lowerWeek are independent objects (not same reference)', () => {
    const result = createEmptySchedule([makeGroup(1, 'A')])
    expect(result.upperWeek).not.toBe(result.lowerWeek)
    expect(result.upperWeek[0]).not.toBe(result.lowerWeek[0])
  })

  it('preserves group id-to-name mapping order', () => {
    const groups = [makeGroup(3, 'C'), makeGroup(1, 'A'), makeGroup(2, 'B')]
    const result = createEmptySchedule(groups)
    const cellIds = result.upperWeek[0]!.timeslots[0]!.groups!.map(c => c.groupId)
    expect(cellIds).toEqual([3, 1, 2])
  })
})
