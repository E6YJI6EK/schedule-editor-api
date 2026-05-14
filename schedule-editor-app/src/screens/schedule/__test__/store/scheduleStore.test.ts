import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useScheduleStore } from '../../model/scheduleStore'
import * as lessonsApi from '@/api/lessons'
import { Course, EducationForm, type Group } from '@/api/types'

vi.mock('@/api/lessons', () => ({
  getSchedule: vi.fn(),
  createLesson: vi.fn(),
  updateLesson: vi.fn(),
  getTimeSlotId: vi.fn(),
}))

// Minimal group fixture
function makeGroup(id: number, name: string): Group {
  return { id, name, course: Course.First, education_form: EducationForm.FullTime, institute_id: 1, discipline_id: 1 }
}

describe('useScheduleStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  // ─── initial state ─────────────────────────────────────────────────────────

  it('currentWeek starts as "upper"', () => {
    expect(useScheduleStore().currentWeek).toBe('upper')
  })

  it('loading starts as false', () => {
    expect(useScheduleStore().loading).toBe(false)
  })

  it('error starts as null', () => {
    expect(useScheduleStore().error).toBeNull()
  })

  // ─── switchWeek ────────────────────────────────────────────────────────────

  it('switchWeek toggles from "upper" to "lower"', () => {
    const store = useScheduleStore()
    store.switchWeek()
    expect(store.currentWeek).toBe('lower')
  })

  it('switchWeek toggles back from "lower" to "upper"', () => {
    const store = useScheduleStore()
    store.switchWeek()
    store.switchWeek()
    expect(store.currentWeek).toBe('upper')
  })

  // ─── getCurrentWeekData / getWeekData ──────────────────────────────────────

  it('getCurrentWeekData returns upperWeek when currentWeek is "upper"', () => {
    const store = useScheduleStore()
    expect(store.getCurrentWeekData()).toBe(store.schedule.upperWeek)
  })

  it('getCurrentWeekData returns lowerWeek when currentWeek is "lower"', () => {
    const store = useScheduleStore()
    store.switchWeek()
    expect(store.getCurrentWeekData()).toBe(store.schedule.lowerWeek)
  })

  it('getWeekData("upperWeek") returns upperWeek', () => {
    const store = useScheduleStore()
    expect(store.getWeekData('upperWeek')).toBe(store.schedule.upperWeek)
  })

  it('getWeekData("lowerWeek") returns lowerWeek', () => {
    const store = useScheduleStore()
    expect(store.getWeekData('lowerWeek')).toBe(store.schedule.lowerWeek)
  })

  // ─── updateCell (incomplete data → local only) ────────────────────────────

  it('updateCell with incomplete data skips API and updates locally', async () => {
    const store = useScheduleStore()
    const day = store.schedule.upperWeek[0]!
    const slot = day.timeslots[0]!

    await store.updateCell('upperWeek', day.day, slot.time, 0, { subject: null })

    expect(lessonsApi.updateLesson).not.toHaveBeenCalled()
    expect(lessonsApi.createLesson).not.toHaveBeenCalled()
  })

  it('updateCell throws when day/time/groupIndex not found', async () => {
    const store = useScheduleStore()
    await expect(
      store.updateCell('upperWeek', 'НесуществующийДень', '00:00–01:00', 0, {})
    ).rejects.toThrow('Не удалось найти ячейку расписания')
  })

  // ─── addNewClass ───────────────────────────────────────────────────────────

  it('addNewClass updates cell data in-place', () => {
    const store = useScheduleStore()
    const day = store.schedule.upperWeek[0]!
    const slot = day.timeslots[0]!
    const classData = {
      subject: { id: 99, name: 'Тест' },
      teacher: null,
      room: null,
      building: null,
    }
    store.addNewClass('upperWeek', day.day, slot.time, 0, classData)
    expect(slot.groups![0]!.subject?.name).toBe('Тест')
  })

  // ─── loadSchedule ──────────────────────────────────────────────────────────

  it('loadSchedule sets loading = true during fetch and false after', async () => {
    let resolveSchedule!: (v: any) => void
    vi.mocked(lessonsApi.getSchedule).mockReturnValue(
      new Promise(res => { resolveSchedule = res })
    )
    const store = useScheduleStore()
    const promise = store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])
    expect(store.loading).toBe(true)
    resolveSchedule({ success: true, data: [] })
    await promise
    expect(store.loading).toBe(false)
  })

  it('loadSchedule with success updates upperWeek schedule', async () => {
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [] })
    const store = useScheduleStore()
    await store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])
    expect(store.schedule.upperWeek).toHaveLength(6)
  })

  it('loadSchedule with success updates lowerWeek schedule when isUpperWeek=false', async () => {
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [] })
    const store = useScheduleStore()
    await store.loadSchedule([1], false, [makeGroup(1, 'ИТ-11')])
    expect(store.schedule.lowerWeek).toHaveLength(6)
  })

  it('loadSchedule on 404 creates empty schedule and keeps error = null', async () => {
    vi.mocked(lessonsApi.getSchedule).mockRejectedValue({ status: 404, response: { status: 404 } })
    const store = useScheduleStore()
    await store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])
    expect(store.error).toBeNull()
    expect(store.schedule.upperWeek).toHaveLength(6)
  })

  it('loadSchedule on non-404 error sets store.error', async () => {
    vi.mocked(lessonsApi.getSchedule).mockRejectedValue(new Error('Server error'))
    const store = useScheduleStore()
    await store.loadSchedule([1], true, [makeGroup(1, 'ИТ-11')])
    expect(store.error).toBeTruthy()
  })

  // ─── loadBothWeeks ─────────────────────────────────────────────────────────

  it('loadBothWeeks with empty groups array is a no-op (no API call)', async () => {
    const store = useScheduleStore()
    await store.loadBothWeeks([], [])
    expect(lessonsApi.getSchedule).not.toHaveBeenCalled()
  })

  it('loadBothWeeks calls getSchedule twice (once per week)', async () => {
    vi.mocked(lessonsApi.getSchedule).mockResolvedValue({ success: true, data: [] })
    const store = useScheduleStore()
    await store.loadBothWeeks([1], [makeGroup(1, 'ИТ-11')])
    expect(lessonsApi.getSchedule).toHaveBeenCalledTimes(2)
  })
})
