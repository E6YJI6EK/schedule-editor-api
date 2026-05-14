import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { Discipline } from './types'

export async function listDisciplines(): Promise<ApiSuccess<Discipline[]>> {
  const res = await http.get<ApiSuccess<Discipline[]>>('/disciplines')
  return res.data
}

export async function searchDisciplines(params?: { name?: string }): Promise<ApiSuccess<Discipline[]>> {
  const res = await http.get<ApiSuccess<Discipline[]>>('/disciplines/search', { params })
  return res.data
}

export async function createDiscipline(payload: { name: string }): Promise<ApiSuccess<Discipline>> {
  const res = await http.post<ApiSuccess<Discipline>>('/disciplines', payload)
  return res.data
}

export async function updateDiscipline(id: number, payload: { name: string }): Promise<ApiSuccess<Discipline>> {
  const res = await http.put<ApiSuccess<Discipline>>(`/disciplines/${id}`, payload)
  return res.data
}

export async function deleteDiscipline(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/disciplines/${id}`)
  return res.data
}
