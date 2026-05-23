<template>
  <div class="grid gap-6">
    <AppPageHeader title="Customers" description="Customer records created by staff and public bookings." />

    <section class="section-grid two">
      <div class="panel p-4">
        <h2 class="mb-4 text-lg font-bold text-zinc-950">Customer list</h2>
        <ResourceState
          :loading="status === 'pending'"
          :error="loadError"
          :empty="customers.length === 0"
          empty-title="No customers yet"
          empty-text="Create a customer or take a public booking."
        >
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers" :key="customer.id">
                  <td class="font-semibold text-zinc-950">{{ customer.name }}</td>
                  <td>{{ customer.email }}</td>
                  <td>{{ customer.phone || 'Not set' }}</td>
                  <td>
                    <div class="flex justify-end gap-2">
                      <UButton color="neutral" variant="ghost" icon="i-lucide-pencil" @click="editCustomer(customer)">
                        Edit
                      </UButton>
                      <UButton :to="`/customers/${customer.id}`" color="neutral" variant="outline" icon="i-lucide-arrow-right">
                        Profile
                      </UButton>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </ResourceState>
      </div>

      <form class="panel grid gap-4 p-4" @submit.prevent="saveCustomer">
        <div class="flex items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-zinc-950">{{ editingCustomer ? 'Edit customer' : 'Add customer' }}</h2>
          <UButton v-if="editingCustomer" color="neutral" variant="ghost" icon="i-lucide-x" @click="resetForm">
            Clear
          </UButton>
        </div>
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
          {{ editingCustomer ? 'Update customer' : 'Add customer' }}
        </UButton>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, Customer } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { data, status, error, refresh } = await useSanctumFetch<ApiCollection<Customer>>('/api/customers', { server: false })
const customers = computed(() => data.value?.data || [])
const loadError = computed(() => error.value ? getApiErrorMessage(error.value, 'Customers could not be loaded.') : '')

const editingCustomer = ref<Customer | null>(null)
const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const form = reactive({
  name: '',
  email: '',
  phone: ''
})

function resetForm() {
  editingCustomer.value = null
  form.name = ''
  form.email = ''
  form.phone = ''
}

function editCustomer(customer: Customer) {
  editingCustomer.value = customer
  form.name = customer.name
  form.email = customer.email
  form.phone = customer.phone || ''
  successMessage.value = ''
  errorMessage.value = ''
}

async function saveCustomer() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const endpoint = editingCustomer.value ? `/api/customers/${editingCustomer.value.id}` : '/api/customers'
    const request = await useSanctumFetch(endpoint, {
      method: editingCustomer.value ? 'PUT' : 'POST',
      body: {
        name: form.name,
        email: form.email,
        phone: form.phone || null
      },
      key: `customer-save-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = editingCustomer.value ? 'Customer updated.' : 'Customer added.'
    resetForm()
    await refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Customer could not be saved.')
  } finally {
    isSaving.value = false
  }
}
</script>
