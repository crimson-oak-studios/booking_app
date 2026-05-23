<template>
  <div class="grid gap-6">
    <AppPageHeader title="Staff" description="Staff users can manage appointments for this business." />

    <section class="section-grid two">
      <div class="panel p-4">
        <h2 class="mb-4 text-lg font-bold text-zinc-950">Staff list</h2>
        <ResourceState
          :loading="status === 'pending'"
          :error="loadError"
          :empty="staff.length === 0"
          empty-title="No staff yet"
          empty-text="Add a staff member to assign appointments."
        >
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in staff" :key="member.id">
                  <td class="font-semibold text-zinc-950">{{ member.name }}</td>
                  <td>{{ member.email }}</td>
                  <td><StatusBadge :value="member.role" /></td>
                </tr>
              </tbody>
            </table>
          </div>
        </ResourceState>
      </div>

      <form class="panel grid gap-4 p-4" @submit.prevent="createStaff">
        <h2 class="text-lg font-bold text-zinc-950">Add staff</h2>
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
          <span class="label">Temporary password</span>
          <input v-model="form.password" class="input" type="password" minlength="8" required>
        </label>

        <UButton type="submit" color="primary" icon="i-lucide-user-plus" :loading="isSaving">
          Add staff member
        </UButton>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, StaffUser } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { data, status, error, refresh } = await useSanctumFetch<ApiCollection<StaffUser>>('/api/staff', { server: false })
const staff = computed(() => data.value?.data || [])
const loadError = computed(() => error.value ? getApiErrorMessage(error.value, 'Staff could not be loaded.') : '')

const form = reactive({
  name: '',
  email: '',
  password: ''
})

const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

async function createStaff() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const request = await useSanctumFetch('/api/staff', {
      method: 'POST',
      body: { ...form },
      key: `staff-create-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    form.name = ''
    form.email = ''
    form.password = ''
    successMessage.value = 'Staff member added.'
    await refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Staff member could not be added.')
  } finally {
    isSaving.value = false
  }
}
</script>
