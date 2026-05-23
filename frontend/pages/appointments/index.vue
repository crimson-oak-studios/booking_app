<template>
  <div class="grid gap-6">
    <AppPageHeader title="Appointments" description="Booked, pending, and cancelled appointments stay visible." />

    <section class="section-grid two">
      <div class="panel p-4">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-bold text-zinc-950">Calendar list</h2>
          <select v-model="statusFilter" class="select max-w-48">
            <option value="all">All statuses</option>
            <option value="active">Active only</option>
            <option value="cancelled">Cancelled only</option>
          </select>
        </div>

        <ResourceState
          :loading="isLoading"
          :error="loadError"
          :empty="filteredAppointments.length === 0"
          empty-title="No appointments found"
          empty-text="Create an appointment or adjust the status filter."
        >
          <div class="grid gap-5">
            <section v-for="group in groupedAppointments" :key="group.date" class="grid gap-3">
              <h3 class="text-sm font-bold uppercase tracking-normal text-zinc-500">{{ group.date }}</h3>
              <article
                v-for="appointment in group.items"
                :key="appointment.id"
                class="grid gap-3 rounded-md border border-zinc-200 bg-white p-3 sm:grid-cols-[84px_1fr_auto]"
                :class="{ 'canceled-appointment': isCanceledStatus(appointment.status) }"
              >
                <div class="rounded-md bg-zinc-100 p-3 text-center">
                  <p class="text-sm font-bold text-zinc-950">{{ formatTime(appointment.starts_at) }}</p>
                  <p class="mt-1 text-xs text-zinc-600">{{ formatTime(appointment.ends_at) }}</p>
                </div>
                <div>
                  <p class="appointment-title font-semibold text-zinc-950">
                    {{ customerName(appointment, customers) }}
                  </p>
                  <p class="mt-1 text-sm text-zinc-600">{{ serviceName(appointment, services) }} · {{ staffName(appointment, staff) }}</p>
                  <p v-if="appointment.notes" class="mt-2 text-sm text-zinc-600">{{ appointment.notes }}</p>
                  <div class="mt-3 flex flex-wrap gap-2">
                    <StatusBadge :value="appointment.status" />
                    <StatusBadge :value="appointment.payment_status" />
                  </div>
                </div>
                <div class="flex flex-wrap gap-2 sm:justify-end">
                  <UButton color="neutral" variant="ghost" icon="i-lucide-pencil" @click="editAppointment(appointment)">
                    Edit
                  </UButton>
                  <UButton
                    v-if="canCancelAppointment(appointment)"
                    color="error"
                    variant="ghost"
                    icon="i-lucide-ban"
                    :disabled="isCanceledStatus(appointment.status)"
                    :loading="cancellingId === appointment.id"
                    @click="cancelAppointment(appointment)"
                  >
                    Cancel
                  </UButton>
                </div>
              </article>
            </section>
          </div>
        </ResourceState>
      </div>

      <form class="panel grid gap-4 p-4" @submit.prevent="saveAppointment">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-zinc-950">{{ editingAppointment ? 'Edit appointment' : 'Create appointment' }}</h2>
          <UButton v-if="editingAppointment" color="neutral" variant="ghost" icon="i-lucide-x" @click="resetForm">
            Clear
          </UButton>
        </div>

        <ApiFeedback :error="errorMessage" :success="successMessage" />

        <label class="field">
          <span class="label">Customer</span>
          <select v-model.number="form.customer_id" class="select" required>
            <option value="" disabled>Select customer</option>
            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
              {{ customer.name }}
            </option>
          </select>
        </label>

        <label class="field">
          <span class="label">Service</span>
          <select v-model.number="form.service_id" class="select" required>
            <option value="" disabled>Select service</option>
            <option v-for="service in activeServices" :key="service.id" :value="service.id">
              {{ service.name }} · {{ service.duration_minutes }} min
            </option>
          </select>
        </label>

        <label class="field">
          <span class="label">Staff</span>
          <select v-model="staffSelection" class="select">
            <option value="">Unassigned</option>
            <option v-for="member in staff" :key="member.id" :value="String(member.id)">
              {{ member.name }}
            </option>
          </select>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="field">
            <span class="label">Starts</span>
            <input v-model="form.starts_at" class="input" type="datetime-local" required>
          </label>
          <label class="field">
            <span class="label">Ends</span>
            <input v-model="form.ends_at" class="input" type="datetime-local" required>
          </label>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="field">
            <span class="label">Status</span>
            <select v-model="form.status" class="select">
              <option value="pending_payment">Pending payment</option>
              <option value="booked">Booked</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </label>
          <label class="field">
            <span class="label">Payment</span>
            <select v-model="form.payment_status" class="select">
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="failed">Failed</option>
              <option value="refunded">Refunded</option>
            </select>
          </label>
        </div>

        <label class="field">
          <span class="label">Notes</span>
          <textarea v-model="form.notes" class="textarea" />
        </label>

        <UButton type="submit" color="primary" icon="i-lucide-save" :loading="isSaving" :disabled="customers.length === 0 || activeServices.length === 0">
          {{ hasPersistedAppointmentId(editingAppointment) ? 'Save appointment' : 'Create appointment' }}
        </UButton>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, Appointment, Customer, Service, StaffUser } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { formatTime, formatWeekday, toDateTimeLocal } = useFormatters()
const { isCanceledStatus, customerName, serviceName, staffName } = useAppointmentHelpers()

const appointmentsRequest = await useSanctumFetch<ApiCollection<Appointment>>('/api/appointments', { server: false })
const customersRequest = await useSanctumFetch<ApiCollection<Customer>>('/api/customers', { server: false })
const servicesRequest = await useSanctumFetch<ApiCollection<Service>>('/api/services', { server: false })
const staffRequest = await useSanctumFetch<ApiCollection<StaffUser>>('/api/staff', { server: false })

const appointments = computed(() => appointmentsRequest.data.value?.data || [])
const customers = computed(() => customersRequest.data.value?.data || [])
const services = computed(() => servicesRequest.data.value?.data || [])
const activeServices = computed(() => services.value.filter((service) => Boolean(service.is_active)))
const staff = computed(() => staffRequest.data.value?.data || [])

const isLoading = computed(() => [
  appointmentsRequest.status.value,
  customersRequest.status.value,
  servicesRequest.status.value,
  staffRequest.status.value
].includes('pending'))

const loadError = computed(() => {
  const error = appointmentsRequest.error.value || customersRequest.error.value || servicesRequest.error.value || staffRequest.error.value
  return error ? getApiErrorMessage(error, 'Appointments could not be loaded.') : ''
})

const statusFilter = ref<'all' | 'active' | 'cancelled'>('all')
const editingAppointment = ref<Appointment | null>(null)
const isSaving = ref(false)
const cancellingId = ref<number | null>(null)
const errorMessage = ref('')
const successMessage = ref('')
const staffSelection = ref('')

const form = reactive({
  customer_id: '' as number | '',
  service_id: '' as number | '',
  starts_at: '',
  ends_at: '',
  status: 'pending_payment',
  payment_status: 'pending',
  notes: ''
})

const filteredAppointments = computed(() => {
  const sorted = [...appointments.value].sort((first, second) => new Date(first.starts_at).getTime() - new Date(second.starts_at).getTime())

  if (statusFilter.value === 'active') {
    return sorted.filter((appointment) => !isCanceledStatus(appointment.status))
  }

  if (statusFilter.value === 'cancelled') {
    return sorted.filter((appointment) => isCanceledStatus(appointment.status))
  }

  return sorted
})

const groupedAppointments = computed(() => {
  const groups = new Map<string, Appointment[]>()

  for (const appointment of filteredAppointments.value) {
    const label = formatWeekday(appointment.starts_at)
    groups.set(label, [...(groups.get(label) || []), appointment])
  }

  return Array.from(groups.entries()).map(([date, items]) => ({ date, items }))
})

watch(() => [form.service_id, form.starts_at], () => {
  if (!form.service_id || !form.starts_at || editingAppointment.value) {
    return
  }

  const service = services.value.find((item) => item.id === form.service_id)
  if (!service) {
    return
  }

  const start = new Date(form.starts_at)
  start.setMinutes(start.getMinutes() + service.duration_minutes)
  form.ends_at = toDateTimeLocal(start.toISOString())
})

function resetForm() {
  editingAppointment.value = null
  form.customer_id = ''
  form.service_id = ''
  form.starts_at = ''
  form.ends_at = ''
  form.status = 'pending_payment'
  form.payment_status = 'pending'
  form.notes = ''
  staffSelection.value = ''
}

function editAppointment(appointment: Appointment) {
  editingAppointment.value = appointment
  form.customer_id = appointment.customer_id
  form.service_id = appointment.service_id
  form.starts_at = toDateTimeLocal(appointment.starts_at)
  form.ends_at = toDateTimeLocal(appointment.ends_at)
  form.status = appointment.status || 'pending_payment'
  form.payment_status = appointment.payment_status || 'pending'
  form.notes = appointment.notes || ''
  staffSelection.value = appointment.staff_user_id ? String(appointment.staff_user_id) : ''
  successMessage.value = ''
  errorMessage.value = ''
}

function appointmentPayload(appointment?: Appointment) {
  return {
    service_id: Number(form.service_id || appointment?.service_id),
    customer_id: Number(form.customer_id || appointment?.customer_id),
    staff_user_id: staffSelection.value ? Number(staffSelection.value) : null,
    starts_at: form.starts_at || appointment?.starts_at,
    ends_at: form.ends_at || appointment?.ends_at,
    status: form.status || appointment?.status || 'pending_payment',
    payment_status: form.payment_status || appointment?.payment_status || 'pending',
    notes: form.notes || null
  }
}

function hasPersistedAppointmentId(appointment: Appointment | null): boolean {
  return Number.isInteger(appointment?.id) && Number(appointment?.id) > 0
}

function canCancelAppointment(appointment: Appointment): boolean {
  return hasPersistedAppointmentId(appointment)
}

async function refreshAppointments() {
  await appointmentsRequest.refresh()
}

async function saveAppointment() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const isExistingAppointment = hasPersistedAppointmentId(editingAppointment.value)
    const endpoint = isExistingAppointment ? `/api/appointments/${editingAppointment.value?.id}` : '/api/appointments'
    const request = await useSanctumFetch(endpoint, {
      method: isExistingAppointment ? 'PUT' : 'POST',
      body: appointmentPayload(editingAppointment.value || undefined),
      key: `appointment-save-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = isExistingAppointment ? 'Appointment updated.' : 'Appointment created.'
    resetForm()
    await refreshAppointments()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Appointment could not be saved.')
  } finally {
    isSaving.value = false
  }
}

async function cancelAppointment(appointment: Appointment) {
  if (!hasPersistedAppointmentId(appointment)) {
    errorMessage.value = 'Only saved appointments can be cancelled.'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''
  cancellingId.value = appointment.id

  try {
    const request = await useSanctumFetch(`/api/appointments/${appointment.id}`, {
      method: 'PUT',
      body: {
        service_id: appointment.service_id,
        customer_id: appointment.customer_id,
        staff_user_id: appointment.staff_user_id,
        starts_at: appointment.starts_at,
        ends_at: appointment.ends_at,
        status: 'cancelled',
        payment_status: appointment.payment_status,
        notes: appointment.notes
      },
      key: `appointment-cancel-${appointment.id}-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = 'Appointment cancelled.'
    await refreshAppointments()
  } catch (cancelError) {
    errorMessage.value = getApiErrorMessage(cancelError, 'Appointment could not be cancelled.')
  } finally {
    cancellingId.value = null
  }
}
</script>
