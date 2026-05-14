import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { DayPartition } from './types'

export async function listDayPartitions(): Promise<ApiSuccess<DayPartition[]>> {
  const res = await http.get<ApiSuccess<DayPartition[]>>('/day-partitions')
  return res.data
}

export async function createDayPartition(payload: { start_time: string; end_time: string }): Promise<ApiSuccess<DayPartition>> {
  const res = await http.post<ApiSuccess<DayPartition>>('/day-partitions', payload)
  return res.data
}

export async function updateDayPartition(id: number, payload: { start_time: string; end_time: string }): Promise<ApiSuccess<DayPartition>> {
  const res = await http.put<ApiSuccess<DayPartition>>(`/day-partitions/${id}`, payload)
  return res.data
}

export async function deleteDayPartition(id: number): Promise<ApiSuccess<null>> {
  const res = await http.delete<ApiSuccess<null>>(`/day-partitions/${id}`)
  return res.data
}
