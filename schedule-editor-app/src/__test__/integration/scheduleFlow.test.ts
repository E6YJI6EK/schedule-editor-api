/**
 * Интеграционные тесты: проверяют совместную работу store + API + transform
 * без запросов к реальному серверу (API замокан).
 */
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useScheduleStore } from '@/screens/schedule/model/scheduleStore'
import * as lessonsApi from '@/api/lessons'
import type { LessonWithRelations } from '@/api/lessons'
import type { Group } from '@/api/types'
import { Course, EducationForm } from '@/api/types'

vi.mock('@/api/lessons', () => ({
  getSchedule: vi.fn(),
  createLesson: vi.fn(),
  updateLesson: vi.fn(),
  getTimeSlotId: vi.fn(),
}))

function makeGroup(id: number, name: string): Group {
  return { id, name, course: Course.First, education_form: EducationForm.FullTime, institute_id: 1, discipline_id: 1 }
}

function makeLesson(groupId: number): LessonWithRelations {
  return {
    id: groupId * 100,
    teacher: { id: 1, name: 'Иванов И.И.' },
    class_room: { id: 1, number: '101', building: { id: 1, short_name: 'А' } },
    time_slot: {
      id: 1,
      week_type: 'upper' as any,
      day: 1, // Понедельник
      day_partition: { id: 1, start_time: '08:30', end_time: '10:00' },
    },
    discipline: { id: 1, name: 'Математика' },
    group: { id: groupId, name: `Группа-${groupId}`, course: 1, education_form: EducationForm.FullTime, institute_id: 1 },
  }
}

describe('Integration: schedule loading flow', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('loadSchedule → transforms API response → stores correct lesson in upperWeek', async () => {
    const lesson = makeLesson(1)
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [lesson] })

    const store = useScheduleStore()
    const groups = [makeGroup(1, 'ИТ-11')]
    await store.loadSchedule([1], true, groups)

    // Monday (index 0), first slot (index 0), first group (index 0)
    const cell = store.schedule.upperWeek[0].timeslots[0].groups![0]
    expect(cell.subject?.name).toBe('Математика')
    expect(cell.teacher?.name).toBe('Иванов И.И.')
    expect(cell.room?.number).toBe('101')
    expect(cell.lessonId).toBe(100)
  })

  it('loadSchedule → other slots remain empty when API returns one lesson', async () => {
    const lesson = makeLesson(1)
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [lesson] })

    const store = useScheduleStore()
    await store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])

    // Tuesday first slot should be empty
    const tuesdayCell = store.schedule.upperWeek[1].timeslots[0].groups![0]
    expect(tuesdayCell.subject).toBeNull()
  })

  it('loadBothWeeks → populates both upperWeek and lowerWeek independently', async () => {
    const upperLesson = makeLesson(1)
    const lowerLesson: LessonWithRelations = {
      ...makeLesson(1),
      id: 999,
      discipline: { id: 2, name: 'Физика' },
      time_slot: { id: 2, week_type: 'lower' as any, day: 2, day_partition: { id: 2, start_time: '10:10', end_time: '11:40' } },
    }

    vi.mocked(lessonsApi.getSchedule)
      .mockResolvedValueOnce({ success: true, data: [upperLesson] }) // upper
      .mockResolvedValueOnce({ success: true, data: [lowerLesson] }) // lower

    const store = useScheduleStore()
    await store.loadBothWeeks([1], [makeGroup(1, 'ИТ-11')])

    // Upper: Mon slot 1 has Математика
    expect(store.schedule.upperWeek[0].timeslots[0].groups![0].subject?.name).toBe('Математика')
    // Lower: Tue slot 2 has Физика
    expect(store.schedule.lowerWeek[1].timeslots[1].groups![0].subject?.name).toBe('Физика')
  })

  it('404 during loadSchedule does not pollute store.error and creates empty week structure', async () => {
    vi.mocked(lessonsApi.getSchedule).mockRejectedValue({ status: 404 })

    const store = useScheduleStore()
    await store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])

    expect(store.error).toBeNull()
    expect(store.schedule.upperWeek).toHaveLength(6)
    store.schedule.upperWeek.forEach(day => {
      day.timeslots.forEach(slot => {
        expect(slot.groups![0].subject).toBeNull()
      })
    })
  })

  it('switchWeek after loadBothWeeks returns correct week data', async () => {
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [] })

    const store = useScheduleStore()
    await store.loadBothWeeks([1], [makeGroup(1, 'ИТ-11')])

    // Default: upper
    expect(store.getCurrentWeekData()).toBe(store.schedule.upperWeek)

    store.switchWeek()
    expect(store.getCurrentWeekData()).toBe(store.schedule.lowerWeek)
  })
})
