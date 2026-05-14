import { describe, it, expect } from 'vitest'
import { cn } from './utils'

describe('cn', () => {
  it('merges class names', () => {
    const result = cn('p-2', 'text-sm', 'p-4')
    expect(result).toBe('text-sm p-4')
  })

  it('handles conditional classes', () => {
    const active = true
    const result = cn('btn', active && 'btn-active', false && 'x')
    expect(result).toBe('btn btn-active')
  })
})

