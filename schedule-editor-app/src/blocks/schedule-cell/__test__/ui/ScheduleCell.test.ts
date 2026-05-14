import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ScheduleCell from '../../view.vue'
import type { ClassData, WeekType } from '@/types/schedule'

// Stub out sub-components that make API calls
vi.mock('@/units/building-select/view.vue', () => ({ default: { template: '<div data-testid="building-select" />' } }))
vi.mock('@/units/room-select/view.vue', () => ({ default: { template: '<div data-testid="room-select" />' } }))
vi.mock('@/units/subject-select/view.vue', () => ({ default: { template: '<div data-testid="subject-select" />' } }))
vi.mock('@/units/teacher-select/view.vue', () => ({ default: { template: '<div data-testid="teacher-select" />' } }))

const emptyCell: ClassData = { subject: null, teacher: null, room: null, building: null }

const filledCell: ClassData = {
  lessonId: 42,
  subject: { id: 1, name: 'Математика' },
  teacher: { id: 1, name: 'Иванов И.И.', discipline_id: 1 },
  room: { id: 1, number: '101', building_id: 1 },
  building: { id: 1, name: 'А' },
  groupId: 1,
}

function mountCell(cellData: ClassData = emptyCell, extra: Record<string, any> = {}) {
  return mount(ScheduleCell, {
    props: {
      cellData,
      day: 'Понедельник',
      time: '08:30–10:00',
      group: 'ИТ-11',
      groupIndex: 0,
      weekType: 'upperWeek' as WeekType,
      updateCell: vi.fn().mockResolvedValue(undefined),
      moveCell: vi.fn().mockResolvedValue(undefined),
      ...extra,
    },
  })
}

describe('ScheduleCell', () => {
  // ─── rendering ─────────────────────────────────────────────────────────────

  it('shows "Пусто" placeholder for an empty cell', () => {
    const wrapper = mountCell()
    expect(wrapper.text()).toContain('Пусто')
  })

  it('shows subject name when cell has lesson data', () => {
    const wrapper = mountCell(filledCell)
    expect(wrapper.text()).toContain('Математика')
  })

  it('shows teacher name when cell has lesson data', () => {
    const wrapper = mountCell(filledCell)
    expect(wrapper.text()).toContain('Иванов И.И.')
  })

  it('shows room number when cell has lesson data', () => {
    const wrapper = mountCell(filledCell)
    expect(wrapper.text()).toContain('101')
  })

  it('does not show "Пусто" when cell has lesson data', () => {
    const wrapper = mountCell(filledCell)
    expect(wrapper.text()).not.toContain('Пусто')
  })

  // ─── edit dialog ────────────────────────────────────────────────────────────

  it('opens edit dialog on cell click', async () => {
    const wrapper = mountCell()
    await wrapper.find('[draggable]').trigger('click')
    expect(wrapper.text()).toContain('Редактировать пару')
  })

  it('shows group name in edit dialog title', async () => {
    const wrapper = mountCell()
    await wrapper.find('[draggable]').trigger('click')
    expect(wrapper.text()).toContain('ИТ-11')
  })

  it('closes dialog when cancel button is clicked', async () => {
    const wrapper = mountCell()
    await wrapper.find('[draggable]').trigger('click')
    const cancelBtn = wrapper.findAll('button').find(b => b.text().includes('Отмена'))!
    await cancelBtn.trigger('click')
    expect(wrapper.text()).not.toContain('Редактировать пару')
  })

  it('calls updateCell when save button is clicked', async () => {
    const updateCell = vi.fn().mockResolvedValue(undefined)
    const wrapper = mountCell(emptyCell, { updateCell })
    await wrapper.find('[draggable]').trigger('click')
    const saveBtn = wrapper.findAll('button').find(b => b.text().includes('Сохранить'))!
    await saveBtn.trigger('click')
    expect(updateCell).toHaveBeenCalledOnce()
  })

  it('passes correct weekType, day, time, groupIndex to updateCell', async () => {
    const updateCell = vi.fn().mockResolvedValue(undefined)
    const wrapper = mountCell(emptyCell, { updateCell })
    await wrapper.find('[draggable]').trigger('click')
    const saveBtn = wrapper.findAll('button').find(b => b.text().includes('Сохранить'))!
    await saveBtn.trigger('click')
    expect(updateCell).toHaveBeenCalledWith('upperWeek', 'Понедельник', '08:30–10:00', 0, expect.any(Object))
  })

  it('shows save error message when updateCell rejects', async () => {
    const updateCell = vi.fn().mockRejectedValue(new Error('Ошибка сервера'))
    const wrapper = mountCell(emptyCell, { updateCell })
    await wrapper.find('[draggable]').trigger('click')
    const saveBtn = wrapper.findAll('button').find(b => b.text().includes('Сохранить'))!
    await saveBtn.trigger('click')
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Ошибка сервера')
  })

  // ─── draggability ───────────────────────────────────────────────────────────

  it('draggable is true for a filled cell', () => {
    const wrapper = mountCell(filledCell)
    const el = wrapper.find('[draggable]')
    expect(el.attributes('draggable')).toBe('true')
  })

  it('draggable is false for an empty cell', () => {
    const wrapper = mountCell()
    const el = wrapper.find('[draggable]')
    expect(el.attributes('draggable')).toBe('false')
  })
})
