export type ApiSuccess<T> = {
  success: true
  message?: string
  data: T
  status?: number
}

export type ApiError = {
  success: false
  message: string
  status: number
  errors?: Record<string, string[]>
}