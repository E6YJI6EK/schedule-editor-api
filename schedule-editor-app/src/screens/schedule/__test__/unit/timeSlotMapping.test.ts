import { describe, it, expect } from 'vitest'
import { getDayNumber } from '../../model/timeSlotMapping'

describe('timeSlotMapping', () => {
  it('maps russian day names to numbers', () => {
    expect(getDayNumber('Понедельник')).toBe(1)
    expect(getDayNumber('Вторник')).toBe(2)
    expect(getDayNumber('Среда')).toBe(3)
    expect(getDayNumber('Четверг')).toBe(4)
    expect(getDayNumber('Пятница')).toBe(5)
    expect(getDayNumber('Суббота')).toBe(6)
    expect(getDayNumber('Воскресенье')).toBe(7)
  })

  it('falls back to 1 for unknown day', () => {
    expect(getDayNumber('Неизвестный')).toBe(1)
  })
})
