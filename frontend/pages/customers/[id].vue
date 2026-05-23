<template>
  <div class="grid gap-6">
    <AppPageHeader
      :title="customer ? customer.name : 'Customer'"
      description="Customer profile and appointment history."
    >
      <template #actions>
        <UButton to="/customers" color="neutral" variant="outline" icon="i-lucide-arrow-left">
          Customers
        </UButton>
      </template>
    </AppPageHeader>

    <ResourceState :loading="isLoading" :error="loadError || notFoundError">
      <section v-if="customer" class="section-grid two">
        <div class="grid gap-4">
          <div class="panel p-4">
            <h2 class="text-lg font-bold text-zinc-950">Profile</h2>
            <dl class="mt-4 grid gap-3 text-sm">
              <div>
                <dt class="font-semibold text-zinc-500">Email</dt>
                <dd class="mt-1 text-zinc-950">{{ customer.email }}</dd>
              </div>
              <div>
                <dt class="font-semibold text-zinc-500">Phone</dt>
                <dd class="mt-1 text-zinc-950">{{ customer.phone || 'Not set' }}</dd>
              </div>
              <div>
                <dt class="font-semibold text-zinc-500">Customer since</dt>
                <dd class="mt-1 text-zinc-950">{{ formatDate(customer.created_at) }}</dd>
              </div>
            </dl>
          </div>

          <div class="panel p-4">
            <h2 class="mb-4 text-lg font-bold text-zinc-950">Appointments</h2>
            <ResourceState
              :empty="customerAppointments.length === 0"
              empty-title="No appointments"
              empty-text="Appointments for this customer will appear here."
            >
              <div class="grid gap-3">
                <article
                  v-for="appointment in customerAppointments"
                  :key="appointment.id"
                  class="rounded-md border border-zinc-200 p-3"
                  :class="{ 'canceled-appointment': isCanceledStatus(appointment.status) }"
                >
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <p class="appointment-title font-semibold text-zinc-950">{{ serviceName(appointment, services) }}</p>
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
        </div>

        <form class="panel grid gap-4 p-4" @submit.prevent="saveCustomer">
          <h2 class="text-lg font-bold text-zinc-950">Edit customer</h2>
          <ApiFeedback :error="errorMessage" :success="successMessage" />

          <label class="field">
            <span class="label">Name</span>
            <input v-model="form.name" class="input" required>
          </label>
          <label class="field">
            <span class="label">Email</span>
            <input v-model="form.email" class="input" type="email" required>
          </label>
          <label class="field">
            <span class="label">Phone</span>
            <input v-model="form.phone" class="input" type="tel">
          </label>

          <UButton type="submit" color="primary" icon="i-lucide-save" :loading="isSaving">
            Save customer
          </UButton>
        </form>
      </section>
    </ResourceState>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, Appointment, Customer, Service } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const route = useRoute()
const customerId = computed(() => Number(route.params.id))
const { formatDate, formatDateTime } = useFormatters()
const { isCanceledStatus, serviceName } = useAppointmentHelpers()

const customersRequest = await useSanctumFetch<ApiCollection<Customer>>('/api/customers', { server: false })
const appointmentsRequest = await useSanctumFetch<ApiCollection<Appointment>>('/api/appointments', { server: false })
const servicesRequest = await useSanctumFetch<ApiCollection<Service>>('/api/services', { server: false })

const customers = computed(() => customersRequest.data.value?.data || [])
const appointments = computed(() => appointmentsRequest.data.value?.data || [])
const services = computed(() => servicesRequest.data.value?.data || [])
const customer = computed(() => customers.value.find((item) => item.id === customerId.value))
const customerAppointments = computed(() => appointments.value.filter((appointment) => appointment.customer_id === customerId.value))

const isLoading = computed(() => [
  customersRequest.status.value,
  appointmentsRequest.status.value,
  servicesRequest.status.value
].includes('pending'))

const loadError = computed(() => {
  const error = customersRequest.error.value || appointmentsRequest.error.value || servicesRequest.error.value
  return error ? getApiErrorMessage(error, 'Customer profile could not be loaded.') : ''
})

const notFoundError = computed(() => {
  if (isLoading.value || customer.value) {
    return ''
  }

  return 'Customer not found.'
})

const form = reactive({
  name: '',
  email: '',
  phone: ''
})

const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

watch(customer, (value) => {
  if (!value) {
    return
  }

  form.name = value.name
  form.email = value.email
  form.phone = value.phone || ''
}, { immediate: true })

async function saveCustomer() {
  if (!customer.value) {
    return
  }

  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const request = await useSanctumFetch(`/api/customers/${customer.value.id}`, {
      method: 'PUT',
      body: {
        name: form.name,
        email: form.email,
        phone: form.phone || null
      },
      key: `customer-profile-save-${customer.value.id}-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = 'Customer updated.'
    await customersRequest.refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Customer could not be saved.')
  } finally {
    isSaving.value = false
  }
}
</script>
