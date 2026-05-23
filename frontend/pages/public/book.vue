<template>
  <div class="mx-auto grid max-w-3xl gap-6">
    <AppPageHeader
      title="Book Appointment"
      description="Square payment is required after the appointment request is created."
    />

    <form class="panel grid gap-4 p-4 sm:p-6" @submit.prevent="bookAppointment">
      <ApiFeedback :error="errorMessage" :success="successMessage" />

      <div class="grid gap-4 sm:grid-cols-2">
        <label class="field">
          <span class="label">Business ID</span>
          <input v-model.number="form.business_id" class="input" type="number" min="1" required>
        </label>
        <label class="field">
          <span class="label">Service ID</span>
          <input v-model.number="form.service_id" class="input" type="number" min="1" required>
        </label>
      </div>

      <label class="field">
        <span class="label">Appointment time</span>
        <input v-model="form.starts_at" class="input" type="datetime-local" required>
      </label>

      <div class="grid gap-4 sm:grid-cols-2">
        <label class="field">
          <span class="label">Name</span>
          <input v-model="form.customer_name" class="input" autocomplete="name" required>
        </label>
        <label class="field">
          <span class="label">Email</span>
          <input v-model="form.customer_email" class="input" type="email" autocomplete="email" required>
        </label>
      </div>

      <label class="field">
        <span class="label">Phone</span>
        <input v-model="form.customer_phone" class="input" type="tel" autocomplete="tel">
      </label>

      <UButton type="submit" color="primary" icon="i-lucide-calendar-plus" :loading="isSubmitting">
        Continue to payment
      </UButton>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { BookingResponse } from '~/types/api'

definePageMeta({
  layout: 'public'
})

const route = useRoute()
const router = useRouter()
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  business_id: Number(route.query.business_id || '') || '',
  service_id: Number(route.query.service_id || '') || '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  starts_at: ''
})

async function bookAppointment() {
  errorMessage.value = ''
  successMessage.value = ''
  isSubmitting.value = true

  try {
    const request = await useSanctumFetch<BookingResponse>('/api/public/bookings', {
      method: 'POST',
      body: {
        business_id: Number(form.business_id),
        service_id: Number(form.service_id),
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_phone: form.customer_phone || null,
        starts_at: form.starts_at
      },
      key: `public-booking-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    const response = request.data.value
    const appointmentId = Number(response?.appointment_id || 0)
    const paymentUrl = String(response?.payment_url || response?.paymentUrl || '').trim()

    if (!Number.isInteger(appointmentId) || appointmentId <= 0) {
      throw new Error('Booking id was not returned.')
    }

    if (!paymentUrl) {
      throw new Error('Payment link was not returned.')
    }

    successMessage.value = 'Appointment request created. Continue to payment.'
    await router.push({
      path: '/public/checkout',
      query: {
        appointment_id: appointmentId,
        payment_url: paymentUrl
      }
    })
  } catch (bookingError) {
    errorMessage.value = getApiErrorMessage(bookingError, 'Booking could not be created.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
