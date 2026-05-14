import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { ClassRoom } from './types'

export async function listClassRooms(): Promise<ApiSuccess<ClassRoom[]>> {
  const res = await http.get<ApiSuccess<ClassRoom[]>>('/class-rooms')
  return res.data
}

export async function searchClassRooms(params: { building_id: number; number?: string }): Promise<ApiSuccess<ClassRoom[]>> {
  const res = await http.get<ApiSuccess<ClassRoom[]>>('/class-rooms/search', { params })
  return res.data
}

export async function createClassRoom(payload: { number: string; building_id: number }): Promise<ApiSuccess<ClassRoom>> {
  const res = await http.post<ApiSuccess<ClassRoom>>('/class-rooms', payload)
  return res.data
}

export async function updateClassRoom(id: number, payload: { number: string; building_id: number }): Promise<ApiSuccess<ClassRoom>> {
  const res = await http.put<ApiSuccess<ClassRoom>>(`/class-rooms/${id}`, payload)
  return res.data
}

export async function deleteClassRoom(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/class-rooms/${id}`)
  return res.data
}
