import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../model/authStore'
import * as authApi from '@/api/auth'

vi.mock('@/api/auth', () => ({
  login: vi.fn(),
  logout: vi.fn(),
  me: vi.fn(),
  registerEmployee: vi.fn(),
  listEmployees: vi.fn(),
  deleteEmployee: vi.fn(),
}))

const mockUser: authApi.AuthUser = {
  id: 1,
  name: 'Администратор',
  email: 'admin@example.com',
  role: 'ADMIN',
}

const employeeUser: authApi.AuthUser = {
  id: 2,
  name: 'Сотрудник',
  email: 'emp@example.com',
  role: 'EMPLOYEE',
}

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  // ─── initial state ─────────────────────────────────────────────────────────

  it('starts with user = null, loading = false, error = null', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  // ─── getters ───────────────────────────────────────────────────────────────

  it('isAuthenticated is false when user is null', () => {
    expect(useAuthStore().isAuthenticated).toBe(false)
  })

  it('isAuthenticated is true after successful login', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    const store = useAuthStore()
    await store.login('admin@example.com', 'pass')
    expect(store.isAuthenticated).toBe(true)
  })

  it('isAdmin is true when user has ADMIN role', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    const store = useAuthStore()
    await store.login('admin@example.com', 'pass')
    expect(store.isAdmin).toBe(true)
  })

  it('isAdmin is false when user has EMPLOYEE role', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: employeeUser })
    const store = useAuthStore()
    await store.login('emp@example.com', 'pass')
    expect(store.isAdmin).toBe(false)
  })

  it('isAdmin is false when no user', () => {
    expect(useAuthStore().isAdmin).toBe(false)
  })

  // ─── login ─────────────────────────────────────────────────────────────────

  it('login returns true and sets user on success', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    const store = useAuthStore()
    const result = await store.login('admin@example.com', 'pass')
    expect(result).toBe(true)
    expect(store.user).toEqual(mockUser)
  })

  it('login returns false and sets error on failure', async () => {
    vi.mocked(authApi.login).mockRejectedValue({ message: 'Неверный пароль' })
    const store = useAuthStore()
    const result = await store.login('wrong@example.com', 'bad')
    expect(result).toBe(false)
    expect(store.user).toBeNull()
    expect(store.error).toBe('Неверный пароль')
  })

  it('login clears error before each attempt', async () => {
    vi.mocked(authApi.login).mockRejectedValueOnce({ message: 'Ошибка' })
    vi.mocked(authApi.login).mockResolvedValueOnce({ success: true, data: mockUser })
    const store = useAuthStore()
    await store.login('a@a.com', 'bad')
    expect(store.error).toBeTruthy()
    await store.login('a@a.com', 'good')
    expect(store.error).toBeNull()
  })

  it('login sets loading = false after completion', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    const store = useAuthStore()
    await store.login('admin@example.com', 'pass')
    expect(store.loading).toBe(false)
  })

  it('login uses fallback error message when err.message is missing', async () => {
    vi.mocked(authApi.login).mockRejectedValue({})
    const store = useAuthStore()
    await store.login('a@a.com', 'bad')
    expect(store.error).toBe('Неверные учетные данные')
  })

  // ─── logout ────────────────────────────────────────────────────────────────

  it('logout clears user', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    vi.mocked(authApi.logout).mockResolvedValue(undefined)
    const store = useAuthStore()
    await store.login('admin@example.com', 'pass')
    await store.logout()
    expect(store.user).toBeNull()
  })

  it('logout clears user even when API call throws', async () => {
    vi.mocked(authApi.login).mockResolvedValue({ success: true, data: mockUser })
    vi.mocked(authApi.logout).mockRejectedValue(new Error('Network error'))
    const store = useAuthStore()
    await store.login('admin@example.com', 'pass')
    // logout re-throws after finally; swallow the error and verify user was cleared
    await store.logout().catch(() => {})
    expect(store.user).toBeNull()
  })

  // ─── initialize ────────────────────────────────────────────────────────────

  it('initialize sets user from /auth/me response', async () => {
    vi.mocked(authApi.me).mockResolvedValue({ success: true, data: mockUser })
    const store = useAuthStore()
    await store.initialize()
    expect(store.user).toEqual(mockUser)
  })

  it('initialize sets user to null when /auth/me throws', async () => {
    vi.mocked(authApi.me).mockRejectedValue(new Error('401'))
    const store = useAuthStore()
    await store.initialize()
    expect(store.user).toBeNull()
  })
})
