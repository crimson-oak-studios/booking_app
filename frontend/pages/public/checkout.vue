<template>
  <div class="mx-auto grid max-w-3xl gap-6">
    <AppPageHeader
      title="Payment Checkout"
      description="Square confirms payment before the backend sends the booking confirmation email."
    />

    <section class="panel grid gap-5 p-4 sm:p-6">
      <ApiFeedback :error="errorMessage" :success="successMessage" />

      <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
        <p class="text-sm font-semibold text-amber-900">Payment required</p>
        <p class="mt-1 text-sm text-amber-800">Appointment #{{ appointmentId || 'pending' }}</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <UButton
          v-if="paymentUrl"
          :to="paymentUrl"
          target="_blank"
          color="primary"
          icon="i-lucide-credit-card"
        >
          Open Square checkout
        </UButton>
        <UButton color="neutral" variant="outline" icon="i-lucide-check-circle" :loading="isConfirming" :disabled="!appointmentId" @click="simulatePaidWebhook">
          Confirm paid
        </UButton>
      </div>

      <UAlert
        v-if="isPaid"
        color="success"
        variant="soft"
        icon="i-lucide-mail-check"
        title="Payment confirmed"
        description="The backend marked the appointment paid and queued the confirmation email."
      />
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const route = useRoute()
const appointmentId = computed(() => Number(route.query.appointment_id || '') || null)
const paymentUrl = computed(() => String(route.query.payment_url || ''))
const isConfirming = ref(false)
const isPaid = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function simulatePaidWebhook() {
  if (!appointmentId.value) {
    errorMessage.value = 'Missing appointment id.'
    return
  }

  errorMessage.value = ''
  successMessage.value = ''
  isConfirming.value = true

  try {
    const request = await useSanctumFetch('/api/webhooks/square', {
      method: 'POST',
      body: {
        appointment_id: appointmentId.value,
        status: 'paid'
      },
      key: `square-webhook-${appointmentId.value}-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    isPaid.value = true
    successMessage.value = 'Payment confirmed.'
  } catch (confirmError) {
    errorMessage.value = getApiErrorMessage(confirmError, 'Payment confirmation could not be completed.')
  } finally {
    isConfirming.value = false
  }
}
</script>
