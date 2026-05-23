<template>
  <div class="grid gap-6">
    <AppPageHeader title="Services" description="Services define duration and Square payment amount." />

    <section class="section-grid two">
      <div class="panel p-4">
        <h2 class="mb-4 text-lg font-bold text-zinc-950">Service list</h2>
        <ResourceState
          :loading="status === 'pending'"
          :error="loadError"
          :empty="services.length === 0"
          empty-title="No services yet"
          empty-text="Add a service before taking bookings."
        >
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Duration</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="service in services" :key="service.id">
                  <td>
                    <p class="font-semibold text-zinc-950">{{ service.name }}</p>
                    <p v-if="service.description" class="mt-1 text-sm text-zinc-600">{{ service.description }}</p>
                  </td>
                  <td>{{ service.duration_minutes }} min</td>
                  <td>{{ formatMoney(service.price_cents) }}</td>
                  <td><StatusBadge :value="Boolean(service.is_active)" /></td>
                  <td>
                    <div class="flex justify-end gap-2">
                      <UButton color="neutral" variant="ghost" icon="i-lucide-pencil" @click="editService(service)">
                        Edit
                      </UButton>
                      <UButton color="error" variant="ghost" icon="i-lucide-trash-2" :loading="deletingId === service.id" @click="deleteService(service)">
                        Delete
                      </UButton>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </ResourceState>
      </div>

      <form class="panel grid gap-4 p-4" @submit.prevent="saveService">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-zinc-950">{{ editingService ? 'Edit service' : 'Add service' }}</h2>
          <UButton v-if="editingService" color="neutral" variant="ghost" icon="i-lucide-x" @click="resetForm">
            Clear
          </UButton>
        </div>
        <ApiFeedback :error="errorMessage" :success="successMessage" />

        <label class="field">
          <span class="label">Name</span>
          <input v-model="form.name" class="input" required>
        </label>
        <label class="field">
          <span class="label">Description</span>
          <textarea v-model="form.description" class="textarea" />
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="field">
            <span class="label">Duration minutes</span>
            <input v-model.number="form.duration_minutes" class="input" type="number" min="5" step="5" required>
          </label>
          <label class="field">
            <span class="label">Price</span>
            <input v-model.number="form.price" class="input" type="number" min="0" step="0.01" required>
          </label>
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700">
          <input v-model="form.is_active" class="size-4 rounded border-zinc-300" type="checkbox">
          Active
        </label>

        <UButton type="submit" color="primary" icon="i-lucide-save" :loading="isSaving">
          {{ editingService ? 'Update service' : 'Add service' }}
        </UButton>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, Service } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { formatMoney } = useFormatters()
const { data, status, error, refresh } = await useSanctumFetch<ApiCollection<Service>>('/api/services', { server: false })
const services = computed(() => data.value?.data || [])
const loadError = computed(() => error.value ? getApiErrorMessage(error.value, 'Services could not be loaded.') : '')

const editingService = ref<Service | null>(null)
const isSaving = ref(false)
const deletingId = ref<number | null>(null)
const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  name: '',
  description: '',
  duration_minutes: 30,
  price: 0,
  is_active: true
})

function resetForm() {
  editingService.value = null
  form.name = ''
  form.description = ''
  form.duration_minutes = 30
  form.price = 0
  form.is_active = true
}

function editService(service: Service) {
  editingService.value = service
  form.name = service.name
  form.description = service.description || ''
  form.duration_minutes = service.duration_minutes
  form.price = service.price_cents / 100
  form.is_active = Boolean(service.is_active)
  successMessage.value = ''
  errorMessage.value = ''
}

async function saveService() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  const body = {
    name: form.name,
    description: form.description || null,
    duration_minutes: Number(form.duration_minutes),
    price_cents: Math.round(Number(form.price) * 100),
    is_active: form.is_active
  }

  try {
    const endpoint = editingService.value ? `/api/services/${editingService.value.id}` : '/api/services'
    const request = await useSanctumFetch(endpoint, {
      method: editingService.value ? 'PUT' : 'POST',
      body,
      key: `service-save-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = editingService.value ? 'Service updated.' : 'Service added.'
    resetForm()
    await refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Service could not be saved.')
  } finally {
    isSaving.value = false
  }
}

async function deleteService(service: Service) {
  errorMessage.value = ''
  successMessage.value = ''
  deletingId.value = service.id

  try {
    const request = await useSanctumFetch(`/api/services/${service.id}`, {
      method: 'DELETE',
      key: `service-delete-${service.id}-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    if (editingService.value?.id === service.id) {
      resetForm()
    }

    successMessage.value = 'Service deleted.'
    await refresh()
  } catch (deleteError) {
    errorMessage.value = getApiErrorMessage(deleteError, 'Service could not be deleted.')
  } finally {
    deletingId.value = null
  }
}
</script>
