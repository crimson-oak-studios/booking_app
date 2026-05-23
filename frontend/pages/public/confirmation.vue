<template>
  <div class="mx-auto grid max-w-3xl gap-6">
    <AppPageHeader
      title="Booking Confirmation"
      description="Use this page after payment is complete."
    />

    <section class="panel grid gap-5 p-4 sm:p-6">
      <ApiFeedback :error="errorMessage" :success="successMessage" />

      <UAlert
        v-if="pageState === 'loading'"
        color="neutral"
        variant="soft"
        icon="i-lucide-loader-circle"
        title="Loading confirmation"
        description="Checking your booking reference."
      />

      <UAlert
        v-else-if="pageState === 'error'"
        color="error"
        variant="soft"
        icon="i-lucide-alert-circle"
        title="Confirmation unavailable"
        :description="errorMessage || 'A valid booking reference is required.'"
      />

      <UAlert
        v-else
        color="success"
        variant="soft"
        icon="i-lucide-check-circle"
        title="Payment complete"
        :description="`Booking #${bookingReference} is confirmed.`"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'public'
})

const route = useRoute()
const pageState = ref<'loading' | 'error' | 'success'>('loading')
const errorMessage = ref('')
const successMessage = ref('')

const bookingReference = computed(() => {
  const raw = route.query.booking || route.query.booking_id || route.query.appointment_id
  const parsed = Number(raw || '')
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null
})

onMounted(() => {
  if (!bookingReference.value) {
    errorMessage.value = 'Missing or invalid booking reference.'
    pageState.value = 'error'
    return
  }

  pageState.value = 'success'
  successMessage.value = 'Reminder email is sent 24 hours before appointment.'
})
</script>
