import type { Appointment, Customer, Service, StaffUser } from '~/types/api'

export function useAppointmentHelpers() {
  function isCanceledStatus(status: string | null | undefined) {
    return ['cancelled', 'canceled', 'cancelled_by_customer', 'canceled_by_customer'].includes((status || '').toLowerCase())
  }

  function customerName(appointment: Appointment, customers: Customer[]) {
    return customers.find((customer) => customer.id === appointment.customer_id)?.name || `Customer #${appointment.customer_id}`
  }

  function serviceName(appointment: Appointment, services: Service[]) {
    return services.find((service) => service.id === appointment.service_id)?.name || `Service #${appointment.service_id}`
  }

  function staffName(appointment: Appointment, staff: StaffUser[]) {
    if (!appointment.staff_user_id) {
      return 'Unassigned'
    }

    return staff.find((member) => member.id === appointment.staff_user_id)?.name || `Staff #${appointment.staff_user_id}`
  }

  return {
    isCanceledStatus,
    customerName,
    serviceName,
    staffName
  }
}
