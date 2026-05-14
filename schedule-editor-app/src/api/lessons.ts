import type { ApiSuccess } from '@/core/fetch-client/types'
import { http } from '@/core/fetch-client/http'
import type { Lesson, WeekType, EducationForm } from './types'

export async function createLesson(payload: {
  teacher_id: number
  class_room_id: number
  time_slot_id: number
  discipline_id: number
  group_id: number
}): Promise<ApiSuccess<Lesson>> {
  const res = await http.post<ApiSuccess<Lesson>>('/lessons', payload)
  return res.data
}

export async function updateLesson(
  id: number,
  payload: Partial<{
    teacher_id: number
    class_room_id: number
    time_slot_id: number
    discipline_id: number
    group_id: number
  }>
): Promise<ApiSuccess<Lesson>> {
  const res = await http.put<ApiSuccess<Lesson>>(`/lessons/${id}`, payload)
  return res.data
}

export type LessonWithRelations = {
  id: number;
  teacher: {
    id: number;
    name: string;
  };
  class_room: {
    id: number;
    number: string;
    building: {
      id: number;
      short_name: string;
    };
  };
  time_slot: {
    id: number;
    week_type: WeekType;
    day: number;
    day_partition: {
      id: number;
      start_time: string;
      end_time: string;
    };
  };
  discipline: {
    id: number;
    name: string;
  };
  group: {
    id: number;
    name: string;
    course: number;
    education_form: EducationForm;
    institute_id: number;
  };
};

export async function getSchedule(params: {
  group_ids: number[]
  is_upper_week: boolean
}): Promise<ApiSuccess<LessonWithRelations[]>> {
  const queryParams = new URLSearchParams()
  params.group_ids.forEach(id => queryParams.append('group_ids[]', id.toString()))
  queryParams.append('is_upper_week', params.is_upper_week ? '1' : '0')
  
  const res = await http.get<ApiSuccess<LessonWithRelations[]>>(`/lessons/schedule?${queryParams.toString()}`)
  return res.data
}

export async function getScheduleByTeacher(params: {
  teacher_id: number
  is_upper_week: boolean
}): Promise<ApiSuccess<LessonWithRelations[]>> {
  const queryParams = new URLSearchParams()
  queryParams.append('teacher_id', params.teacher_id.toString())
  queryParams.append('is_upper_week', params.is_upper_week ? '1' : '0')

  const res = await http.get<ApiSuccess<LessonWithRelations[]>>(`/lessons/schedule/by-teacher?${queryParams.toString()}`)
  return res.data
}

export async function getScheduleByClassroom(params: {
  classroom_id: number
  is_upper_week: boolean
}): Promise<ApiSuccess<LessonWithRelations[]>> {
  const queryParams = new URLSearchParams()
  queryParams.append('classroom_id', params.classroom_id.toString())
  queryParams.append('is_upper_week', params.is_upper_week ? '1' : '0')

  const res = await http.get<ApiSuccess<LessonWithRelations[]>>(`/lessons/schedule/by-classroom?${queryParams.toString()}`)
  return res.data
}

export async function getTimeSlotId(params: {
  week_type: 'upper' | 'lower'
  day: number
  day_partition_id: number
}): Promise<ApiSuccess<{ id: number }>> {
  const queryParams = new URLSearchParams()
  queryParams.append('week_type', params.week_type)
  queryParams.append('day', params.day.toString())
  queryParams.append('day_partition_id', params.day_partition_id.toString())
  
  const res = await http.get<ApiSuccess<{ id: number }>>(`/lessons/time-slot?${queryParams.toString()}`)
  return res.data
}

