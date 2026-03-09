# Frontend testing with Vitest

## Setup

- Vitest and Vue Test Utils are installed as dev dependencies.
- Vite config includes test options with jsdom environment and globals.

## Scripts

- Run tests once: `npm run test`
- Watch mode: `npm run test:watch`
- UI mode: `npm run test:ui`

## Test locations

- Unit tests live next to source or under `src/**`. Examples:
  - `src/core/lib/utils.test.ts`
  - `src/screens/schedule/__test__/unit/timeSlotMapping.test.ts`
  - `src/blocks/schedule-toolbar/__test__/ui/WeekSwitcher.test.ts`

## Writing tests

- Unit tests use `describe`, `it`, and `expect` globals.
- Component tests use `@vue/test-utils` and run in `jsdom`.

### Example: utility

```ts
import { describe, it, expect } from 'vitest'
import { cn } from './utils'

describe('cn', () => {
  it('merges classes', () => {
    expect(cn('p-2', 'p-4')).toBe('p-4')
  })
})
```

### Example: component

```ts
import { mount } from '@vue/test-utils'
import { vi, describe, it, expect } from 'vitest'
import WeekSwitcher from './WeekSwitcher.vue'

describe('WeekSwitcher', () => {
  it('calls switchWeek', async () => {
    const onSwitch = vi.fn()
    const wrapper = mount(WeekSwitcher, { props: { currentWeek: 'upper', switchWeek: onSwitch } })
    await wrapper.find('button').trigger('click')
    expect(onSwitch).toHaveBeenCalled()
  })
})
```

## TypeScript

- `tsconfig.app.json` includes `vitest/globals` for type-safe test APIs.

## Tips

- Prefer colocated tests near the code they verify.
- Use `@` alias for imports from `src/` as in app code.

