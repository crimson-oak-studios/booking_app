<template>
  <div class="mx-auto grid max-w-3xl gap-6">
    <AppPageHeader
      title="Payment Checkout"
      description="Square confirms payment before the backend sends the booking confirmation email."
    />

    <section class="panel grid gap-5 p-4 sm:p-6">
      <ApiFeedback :error="errorMessage" :success="successMessage" />

      <UAlert
        v-if="pageState === 'loading'"
        color="neutral"
        variant="soft"
        icon="i-lucide-loader-circle"
        title="Loading checkout"
        description="Preparing your payment details."
      />

      <UAlert
        v-else-if="pageState === 'error'"
        color="error"
        variant="soft"
        icon="i-lucide-alert-circle"
        title="Checkout unavailable"
        :description="errorMessage || 'A valid booking id and payment link are required.'"
      />

      <div v-else class="rounded-md border border-amber-200 bg-amber-50 p-4">
        <p class="text-sm font-semibold text-amber-900">Payment required</p>
        <p class="mt-1 text-sm text-amber-800">Appointment #{{ appointmentId }}</p>
      </div>

      <div v-if="pageState === 'ready'" class="flex flex-wrap gap-3">
        <UButton
          v-if="paymentUrl"
          :to="paymentUrl"
          target="_blank"
          color="primary"
          icon="i-lucide-credit-card"
        >
          Continue to payment
        </UButton>
      </div>

      <UAlert
        v-if="pageState === 'ready'"
        color="neutral"
        variant="soft"
        icon="i-lucide-badge-info"
        title="Confirmation happens after payment"
        description="Finish payment first. Confirmation is only shown after payment is complete."
      />

      <UAlert
        v-if="pageState === 'success'"
        color="success"
        variant="soft"
        icon="i-lucide-mail-check"
        title="Payment confirmed"
        description="Payment is complete. You can close this page."
      />
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const route = useRoute()
const pageState = ref<'loading' | 'error' | 'ready' | 'success'>('loading')
const appointmentId = computed(() => {
  const parsed = Number(route.query.appointment_id || '')
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null
})
const paymentUrl = computed(() => String(route.query.payment_url || route.query.paymentUrl || '').trim())
const errorMessage = ref('')
const successMessage = ref('')

onMounted(() => {
  if (!appointmentId.value) {
    errorMessage.value = 'Missing or invalid appointment id.'
    pageState.value = 'error'
    return
  }

  if (!paymentUrl.value) {
    errorMessage.value = 'Missing payment link.'
    pageState.value = 'error'
    return
  }

  const paymentStatus = String(route.query.payment_status || route.query.paymentStatus || '').toLowerCase()
  if (paymentStatus === 'paid') {
    successMessage.value = 'Payment confirmed.'
    pageState.value = 'success'
    return
  }

  pageState.value = 'ready'
})
</script>
