<template>
  <div class="grid gap-6">
    <AppPageHeader
      title="Dashboard"
      :description="business ? business.name : 'Appointment workspace'"
    >
      <template #actions>
        <UButton to="/appointments" color="primary" icon="i-lucide-calendar-plus">
          New appointment
        </UButton>
        <UButton to="/public/book" color="neutral" variant="outline" icon="i-lucide-external-link">
          Public booking
        </UButton>
      </template>
    </AppPageHeader>

    <ResourceState :loading="isLoading" :error="loadError">
      <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="stat in stats" :key="stat.label" class="panel p-4">
          <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-zinc-600">{{ stat.label }}</p>
            <UIcon :name="stat.icon" class="size-5" :class="stat.iconClass" />
          </div>
          <p class="mt-3 text-3xl font-bold text-zinc-950">{{ stat.value }}</p>
        </div>
      </section>

      <section class="section-grid two">
        <div class="panel p-4">
          <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-zinc-950">Upcoming appointments</h2>
            <UButton to="/appointments" color="neutral" variant="ghost" icon="i-lucide-arrow-right">
              View all
            </UButton>
          </div>

          <ResourceState
            :empty="upcomingAppointments.length === 0"
            empty-title="No upcoming appointments"
            empty-text="New bookings will appear here."
          >
            <div class="grid gap-3">
              <article
                v-for="appointment in upcomingAppointments"
                :key="appointment.id"
                class="rounded-md border border-zinc-200 p-3"
                :class="{ 'canceled-appointment': isCanceledStatus(appointment.status) }"
              >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <p class="appointment-title font-semibold text-zinc-950">
                      {{ customerName(appointment, customers) }} with {{ serviceName(appointment, services) }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-600">{{ formatDateTime(appointment.starts_at) }}</p>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <StatusBadge :value="appointment.status" />
                    <StatusBadge :value="appointment.payment_status" />
                  </div>
                </div>
              </article>
            </div>
          </ResourceState>
        </div>

        <div class="grid gap-4">
          <div class="panel p-4">
            <h2 class="text-lg font-bold text-zinc-950">Payment queue</h2>
            <p class="mt-3 text-3xl font-bold text-amber-700">{{ pendingPaymentCount }}</p>
            <p class="mt-1 text-sm text-zinc-600">appointments pending Square payment</p>
          </div>
          <div class="panel p-4">
            <h2 class="text-lg font-bold text-zinc-950">Manual blocks</h2>
            <p class="mt-3 text-3xl font-bold text-indigo-700">{{ timeBlocks.length }}</p>
            <UButton class="mt-4" to="/settings" color="neutral" variant="outline" icon="i-lucide-ban">
              Manage blocks
            </UButton>
          </div>
        </div>
      </section>
    </ResourceState>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, ApiResource, Appointment, Business, Customer, Service, StaffUser, TimeBlock } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { formatDateTime } = useFormatters()
const { customerName, serviceName, isCanceledStatus } = useAppointmentHelpers()

const businessRequest = await useSanctumFetch<ApiResource<Business>>('/api/business-profile', { server: false })
const servicesRequest = await useSanctumFetch<ApiCollection<Service>>('/api/services', { server: false })
const customersRequest = await useSanctumFetch<ApiCollection<Customer>>('/api/customers', { server: false })
const staffRequest = await useSanctumFetch<ApiCollection<StaffUser>>('/api/staff', { server: false })
const appointmentsRequest = await useSanctumFetch<ApiCollection<Appointment>>('/api/appointments', { server: false })
const blocksRequest = await useSanctumFetch<ApiCollection<TimeBlock>>('/api/time-blocks', { server: false })

const business = computed(() => businessRequest.data.value?.data)
const services = computed(() => servicesRequest.data.value?.data || [])
const customers = computed(() => customersRequest.data.value?.data || [])
const staff = computed(() => staffRequest.data.value?.data || [])
const appointments = computed(() => appointmentsRequest.data.value?.data || [])
const timeBlocks = computed(() => blocksRequest.data.value?.data || [])

const isLoading = computed(() => [
  businessRequest.status.value,
  servicesRequest.status.value,
  customersRequest.status.value,
  staffRequest.status.value,
  appointmentsRequest.status.value,
  blocksRequest.status.value
].includes('pending'))

const loadError = computed(() => {
  const error = businessRequest.error.value || servicesRequest.error.value || customersRequest.error.value || staffRequest.error.value || appointmentsRequest.error.value || blocksRequest.error.value
  return error ? getApiErrorMessage(error, 'Dashboard data could not be loaded.') : ''
})

const activeAppointments = computed(() => appointments.value.filter((appointment) => !isCanceledStatus(appointment.status)))
const pendingPaymentCount = computed(() => appointments.value.filter((appointment) => appointment.payment_status === 'pending').length)
const upcomingAppointments = computed(() => {
  const now = Date.now()
  return appointments.value
    .filter((appointment) => new Date(appointment.starts_at).getTime() >= now)
    .sort((first, second) => new Date(first.starts_at).getTime() - new Date(second.starts_at).getTime())
    .slice(0, 5)
})

const stats = computed(() => [
  { label: 'Active appointments', value: activeAppointments.value.length, icon: 'i-lucide-calendar-check', iconClass: 'text-teal-700' },
  { label: 'Customers', value: customers.value.length, icon: 'i-lucide-users', iconClass: 'text-indigo-700' },
  { label: 'Services', value: services.value.length, icon: 'i-lucide-briefcase-business', iconClass: 'text-cyan-700' },
  { label: 'Staff', value: staff.value.length, icon: 'i-lucide-user-round-check', iconClass: 'text-emerald-700' }
])
</script>
