<template>
  <div class="grid gap-6">
    <AppPageHeader title="Business Profile" description="Core business details for the appointment workspace." />

    <ResourceState :loading="status === 'pending'" :error="loadError">
      <section class="section-grid two">
        <form class="panel grid gap-4 p-4" @submit.prevent="saveProfile">
          <ApiFeedback :error="errorMessage" :success="successMessage" />

          <label class="field">
            <span class="label">Business name</span>
            <input v-model="form.name" class="input" required>
          </label>
          <label class="field">
            <span class="label">Email</span>
            <input v-model="form.email" class="input" type="email">
          </label>
          <label class="field">
            <span class="label">Phone</span>
            <input v-model="form.phone" class="input" type="tel">
          </label>
          <label class="field">
            <span class="label">Timezone</span>
            <input v-model="form.timezone" class="input" required>
          </label>

          <div class="flex justify-end">
            <UButton type="submit" color="primary" icon="i-lucide-save" :loading="isSaving">
              Save profile
            </UButton>
          </div>
        </form>

        <aside class="panel p-4">
          <h2 class="text-lg font-bold text-zinc-950">Current profile</h2>
          <dl class="mt-4 grid gap-3 text-sm">
            <div>
              <dt class="font-semibold text-zinc-500">Name</dt>
              <dd class="mt-1 text-zinc-950">{{ business?.name || 'Not set' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-zinc-500">Email</dt>
              <dd class="mt-1 text-zinc-950">{{ business?.email || 'Not set' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-zinc-500">Phone</dt>
              <dd class="mt-1 text-zinc-950">{{ business?.phone || 'Not set' }}</dd>
            </div>
            <div>
              <dt class="font-semibold text-zinc-500">Timezone</dt>
              <dd class="mt-1 text-zinc-950">{{ business?.timezone || 'Not set' }}</dd>
            </div>
          </dl>
        </aside>
      </section>
    </ResourceState>
  </div>
</template>

<script setup lang="ts">
import type { ApiResource, Business } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { data, status, error, refresh } = await useSanctumFetch<ApiResource<Business>>('/api/business-profile', { server: false })
const business = computed(() => data.value?.data)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  timezone: 'America/Chicago'
})

const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const loadError = computed(() => error.value ? getApiErrorMessage(error.value, 'Business profile could not be loaded.') : '')

watch(business, (value) => {
  if (!value) {
    return
  }

  form.name = value.name || ''
  form.email = value.email || ''
  form.phone = value.phone || ''
  form.timezone = value.timezone || 'America/Chicago'
}, { immediate: true })

async function saveProfile() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const request = await useSanctumFetch<ApiResource<Business>>('/api/business-profile', {
      method: 'PUT',
      body: { ...form },
      key: `business-profile-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = 'Business profile updated.'
    await refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Business profile could not be saved.')
  } finally {
    isSaving.value = false
  }
}
</script>
