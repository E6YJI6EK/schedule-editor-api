import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import WeekSwitcher from '../../view/WeekSwitcher.vue'

describe('WeekSwitcher', () => {
  it('renders upper week active state', () => {
    const wrapper = mount(WeekSwitcher, {
      props: {
        currentWeek: 'upper',
        switchWeek: vi.fn(),
      },
    })
    expect(wrapper.text()).toContain('Верхняя')
    expect(wrapper.text()).toContain('Нижняя')
    const spans = wrapper.findAll('span')
    const upper = spans.find(s => s.text().includes('Верхняя'))!
    expect(upper.classes().join(' ')).toMatch(/text-blue-600|font-semibold/)
  })

  it('calls switchWeek on button click', async () => {
    const onSwitch = vi.fn()
    const wrapper = mount(WeekSwitcher, {
      props: {
        currentWeek: 'upper',
        switchWeek: onSwitch,
      },
    })
    await wrapper.find('button').trigger('click')
    expect(onSwitch).toHaveBeenCalledTimes(1)
  })

  it('renders lower week active state with correct classes', () => {
    const wrapper = mount(WeekSwitcher, {
      props: {
        currentWeek: 'lower',
        switchWeek: vi.fn(),
      },
    })
    const spans = wrapper.findAll('span')
    const lower = spans.find(s => s.text().includes('Нижняя'))!
    expect(lower.classes().join(' ')).toMatch(/text-blue-600|font-semibold/)
  })

  it('toggle button has bg-blue-600 class when lower week is active', () => {
    const wrapper = mount(WeekSwitcher, {
      props: {
        currentWeek: 'lower',
        switchWeek: vi.fn(),
      },
    })
    const btn = wrapper.find('button')
    expect(btn.classes()).toContain('bg-blue-600')
  })

  it('toggle button has bg-gray-200 class when upper week is active', () => {
    const wrapper = mount(WeekSwitcher, {
      props: {
        currentWeek: 'upper',
        switchWeek: vi.fn(),
      },
    })
    const btn = wrapper.find('button')
    expect(btn.classes()).toContain('bg-gray-200')
  })

  it('calls switchWeek exactly once per click even with multiple buttons', async () => {
    const onSwitch = vi.fn()
    const wrapper = mount(WeekSwitcher, {
      props: { currentWeek: 'upper', switchWeek: onSwitch },
    })
    await wrapper.find('button').trigger('click')
    await wrapper.find('button').trigger('click')
    expect(onSwitch).toHaveBeenCalledTimes(2)
  })
})
