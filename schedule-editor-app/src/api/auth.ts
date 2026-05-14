import { http } from '@/core/fetch-client/http'
import type { ApiSuccess } from '@/core/fetch-client/types'

export type AuthUser = {
  id: number
  name: string
  email: string
  role: 'ADMIN' | 'EMPLOYEE'
}

export async function login(credentials: {
  email: string
  password: string
}): Promise<ApiSuccess<AuthUser>> {
  const res = await http.post<ApiSuccess<AuthUser>>('/auth/login', credentials)
  return res.data
}

export async function logout(): Promise<void> {
  await http.post('/auth/logout')
}

export async function me(): Promise<ApiSuccess<AuthUser>> {
  const res = await http.get<ApiSuccess<AuthUser>>('/auth/me')
  return res.data
}

export async function registerEmployee(payload: {
  name: string
  email: string
  password: string
  password_confirmation: string
}): Promise<ApiSuccess<AuthUser>> {
  const res = await http.post<ApiSuccess<AuthUser>>('/auth/register', payload)
  return res.data
}

export async function listEmployees(): Promise<ApiSuccess<AuthUser[]>> {
  const res = await http.get<ApiSuccess<AuthUser[]>>('/employees')
  return res.data
}

export async function deleteEmployee(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/employees/${id}`)
  return res.data
}
