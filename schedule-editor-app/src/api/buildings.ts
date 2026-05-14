import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { Building } from './types'

export async function listBuildings(): Promise<ApiSuccess<Building[]>> {
  const res = await http.get<ApiSuccess<Building[]>>('/buildings')
  return res.data
}

export async function searchBuildings(params?: { name?: string }): Promise<ApiSuccess<Building[]>> {
  const res = await http.get<ApiSuccess<Building[]>>('/buildings/search', { params })
  return res.data
}

export async function createBuilding(payload: { name: string }): Promise<ApiSuccess<Building>> {
  const res = await http.post<ApiSuccess<Building>>('/buildings', payload)
  return res.data
}

export async function updateBuilding(id: number, payload: { name: string }): Promise<ApiSuccess<Building>> {
  const res = await http.put<ApiSuccess<Building>>(`/buildings/${id}`, payload)
  return res.data
}

export async function deleteBuilding(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/buildings/${id}`)
  return res.data
}
