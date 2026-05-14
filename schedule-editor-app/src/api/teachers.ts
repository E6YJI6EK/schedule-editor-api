import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { Teacher } from './types'

export async function listTeachers(): Promise<ApiSuccess<Teacher[]>> {
  const res = await http.get<ApiSuccess<Teacher[]>>('/teachers')
  return res.data
}

export async function searchTeachers(params: { discipline_id: number; name?: string }): Promise<ApiSuccess<Teacher[]>> {
  const res = await http.get<ApiSuccess<Teacher[]>>('/teachers/search', { params })
  return res.data
}

export async function searchTeachersByName(params?: { name?: string }): Promise<ApiSuccess<Teacher[]>> {
  const res = await http.get<ApiSuccess<Teacher[]>>('/teachers/search', { params })
  return res.data
}

export async function createTeacher(payload: { name: string; discipline_ids: number[] }): Promise<ApiSuccess<Teacher>> {
  const res = await http.post<ApiSuccess<Teacher>>('/teachers', payload)
  return res.data
}

export async function updateTeacher(id: number, payload: { name: string; discipline_ids: number[] }): Promise<ApiSuccess<Teacher>> {
  const res = await http.put<ApiSuccess<Teacher>>(`/teachers/${id}`, payload)
  return res.data
}

export async function deleteTeacher(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/teachers/${id}`)
  return res.data
}
