import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { useToast } from '../../model/hooks/useToast'

describe('useToast', () => {
  let toast: ReturnType<typeof useToast>

  beforeEach(() => {
    toast = useToast()
    // Clear shared singleton state between tests
    toast.toasts.value.splice(0)
  })

  afterEach(() => {
    vi.useRealTimers()
    toast.toasts.value.splice(0)
  })

  // ─── show ──────────────────────────────────────────────────────────────────

  it('show() adds a toast to the list', () => {
    toast.show('Hello', 'info', 0)
    expect(toast.toasts.value).toHaveLength(1)
    expect(toast.toasts.value[0].message).toBe('Hello')
    expect(toast.toasts.value[0].type).toBe('info')
  })

  it('show() returns a numeric id', () => {
    const id = toast.show('Hello', 'info', 0)
    expect(typeof id).toBe('number')
  })

  it('show() returns unique ids for successive calls', () => {
    const id1 = toast.show('A', 'info', 0)
    const id2 = toast.show('B', 'info', 0)
    expect(id1).not.toBe(id2)
  })

  it('show() auto-removes toast after specified duration', () => {
    vi.useFakeTimers()
    toast.show('Temp', 'info', 1000)
    expect(toast.toasts.value).toHaveLength(1)
    vi.advanceTimersByTime(1001)
    expect(toast.toasts.value).toHaveLength(0)
  })

  it('show() does NOT auto-remove when duration is 0', () => {
    vi.useFakeTimers()
    toast.show('Persistent', 'info', 0)
    vi.advanceTimersByTime(60_000)
    expect(toast.toasts.value).toHaveLength(1)
  })

  // ─── remove ────────────────────────────────────────────────────────────────

  it('remove() deletes the toast with the given id', () => {
    const id = toast.show('Hello', 'info', 0)
    toast.remove(id)
    expect(toast.toasts.value).toHaveLength(0)
  })

  it('remove() with unknown id does nothing', () => {
    toast.show('Hello', 'info', 0)
    toast.remove(9999)
    expect(toast.toasts.value).toHaveLength(1)
  })

  // ─── convenience helpers ───────────────────────────────────────────────────

  it('error() adds a toast with type "error"', () => {
    toast.error('Ошибка', 0)
    expect(toast.toasts.value[0].type).toBe('error')
  })

  it('success() adds a toast with type "success"', () => {
    toast.success('Успех', 0)
    expect(toast.toasts.value[0].type).toBe('success')
  })

  it('info() adds a toast with type "info"', () => {
    toast.info('Инфо', 0)
    expect(toast.toasts.value[0].type).toBe('info')
  })

  it('warning() adds a toast with type "warning"', () => {
    toast.warning('Внимание', 0)
    expect(toast.toasts.value[0].type).toBe('warning')
  })

  it('multiple toasts can coexist in the list', () => {
    toast.show('A', 'info', 0)
    toast.show('B', 'error', 0)
    toast.show('C', 'success', 0)
    expect(toast.toasts.value).toHaveLength(3)
  })
})
