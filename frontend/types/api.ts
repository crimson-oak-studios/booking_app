export interface ApiResource<T> {
  data: T
}

export interface ApiCollection<T> {
  data: T[]
}

export interface Business {
  id: number
  name: string
  email: string | null
  phone: string | null
  timezone: string
  created_at?: string
  updated_at?: string
}

export interface StaffUser {
  id: number
  business_id: number
  name: string
  email: string
  role: string
  created_at?: string
  updated_at?: string
}

export interface Service {
  id: number
  business_id: number
  name: string
  description: string | null
  duration_minutes: number
  price_cents: number
  is_active: boolean | number
  created_at?: string
  updated_at?: string
}

export interface Customer {
  id: number
  business_id: number
  name: string
  email: string
  phone: string | null
  created_at?: string
  updated_at?: string
}

export interface Appointment {
  id: number
  business_id: number
  service_id: number
  customer_id: number
  staff_user_id: number | null
  starts_at: string
  ends_at: string
  status: string
  payment_status: string
  square_payment_link_id: string | null
  notes: string | null
  created_at?: string
  updated_at?: string
}

export interface TimeBlock {
  id: number
  business_id: number
  staff_user_id: number | null
  starts_at: string
  ends_at: string
  reason: string | null
  created_at?: string
  updated_at?: string
}

export interface BookingResponse {
  appointment_id: number
  payment_url: string
}

export interface ValidationErrors {
  [key: string]: string[]
}
