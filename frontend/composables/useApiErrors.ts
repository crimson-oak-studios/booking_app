import type { ValidationErrors } from '~/types/api'

export function getApiFieldErrors(error: unknown): ValidationErrors {
  const value = error as { data?: { errors?: ValidationErrors } } | null
  return value?.data?.errors || {}
}

export function getApiErrorMessage(error: unknown, fallback = 'Something went wrong.'): string {
  const value = error as {
    data?: { message?: string, errors?: ValidationErrors }
    message?: string
    statusMessage?: string
  } | null

  if (value?.data?.message) {
    return value.data.message
  }

  const firstError = Object.values(value?.data?.errors || {})[0]?.[0]
  if (firstError) {
    return firstError
  }

  return value?.statusMessage || value?.message || fallback
}
