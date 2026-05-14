import { defineStore } from 'pinia'
import * as authApi from '@/api/auth'
import type { AuthUser } from '@/api/auth'

interface AuthState {
  user: AuthUser | null
  loading: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state): boolean => !!state.user,
    isAdmin: (state): boolean => state.user?.role === 'ADMIN',
  },
  actions: {
    async initialize() {
      try {
        const res = await authApi.me()
        this.user = res.data
      } catch {
        this.user = null
      }
    },
    async login(email: string, password: string): Promise<boolean> {
      this.loading = true
      this.error = null
      try {
        const res = await authApi.login({ email, password })
        this.user = res.data
        return true
      } catch (err: any) {
        this.error = err?.message || 'Неверные учетные данные'
        return false
      } finally {
        this.loading = false
      }
    },
    async logout() {
      try {
        await authApi.logout()
      } finally {
        this.user = null
      }
    },
  },
})
