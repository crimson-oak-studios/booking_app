<template>
  <div class="grid gap-6">
    <AppPageHeader title="Settings" description="Reminder timing and manual availability blocks." />

    <section class="grid gap-4 lg:grid-cols-[0.85fr_1.15fr]">
      <div class="grid gap-4">
        <div class="panel p-4">
          <div class="flex items-start gap-3">
            <div class="grid size-10 place-items-center rounded-md bg-teal-50 text-teal-800">
              <UIcon name="i-lucide-bell-ring" class="size-5" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-zinc-950">Email reminder</h2>
              <p class="mt-1 text-sm text-zinc-600">24 hours before appointment</p>
            </div>
          </div>
        </div>

        <form class="panel grid gap-4 p-4" @submit.prevent="saveBlock">
          <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-zinc-950">{{ editingBlock ? 'Edit block' : 'Add block' }}</h2>
            <UButton v-if="editingBlock" color="neutral" variant="ghost" icon="i-lucide-x" @click="resetForm">
              Clear
            </UButton>
          </div>
          <ApiFeedback :error="errorMessage" :success="successMessage" />

          <label class="field">
            <span class="label">Staff</span>
            <select v-model="staffSelection" class="select">
              <option value="">All staff</option>
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

          <label class="field">
            <span class="label">Reason</span>
            <input v-model="form.reason" class="input">
          </label>

          <UButton type="submit" color="primary" icon="i-lucide-save" :loading="isSaving">
            {{ editingBlock ? 'Update block' : 'Add block' }}
          </UButton>
        </form>
      </div>

      <div class="panel p-4">
        <h2 class="mb-4 text-lg font-bold text-zinc-950">Manual blocks</h2>
        <ResourceState
          :loading="isLoading"
          :error="loadError"
          :empty="blocks.length === 0"
          empty-title="No blocks"
          empty-text="Manual availability blocks will appear here."
        >
          <div class="grid gap-3">
            <article v-for="block in blocks" :key="block.id" class="rounded-md border border-zinc-200 bg-white p-3">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <p class="font-semibold text-zinc-950">{{ formatDateTime(block.starts_at) }}</p>
                  <p class="mt-1 text-sm text-zinc-600">Ends {{ formatDateTime(block.ends_at) }}</p>
                  <p class="mt-1 text-sm text-zinc-600">{{ staffLabel(block.staff_user_id) }} · {{ block.reason || 'No reason' }}</p>
                </div>
                <UButton color="neutral" variant="ghost" icon="i-lucide-pencil" @click="editBlock(block)">
                  Edit
                </UButton>
              </div>
            </article>
          </div>
        </ResourceState>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import type { ApiCollection, StaffUser, TimeBlock } from '~/types/api'

definePageMeta({
  middleware: ['sanctum:auth']
})

const { formatDateTime, toDateTimeLocal } = useFormatters()
const blocksRequest = await useSanctumFetch<ApiCollection<TimeBlock>>('/api/time-blocks', { server: false })
const staffRequest = await useSanctumFetch<ApiCollection<StaffUser>>('/api/staff', { server: false })

const blocks = computed(() => blocksRequest.data.value?.data || [])
const staff = computed(() => staffRequest.data.value?.data || [])
const isLoading = computed(() => [blocksRequest.status.value, staffRequest.status.value].includes('pending'))
const loadError = computed(() => {
  const error = blocksRequest.error.value || staffRequest.error.value
  return error ? getApiErrorMessage(error, 'Settings could not be loaded.') : ''
})

const editingBlock = ref<TimeBlock | null>(null)
const isSaving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const staffSelection = ref('')
const form = reactive({
  starts_at: '',
  ends_at: '',
  reason: ''
})

function staffLabel(staffId: number | null) {
  if (!staffId) {
    return 'All staff'
  }

  return staff.value.find((member) => member.id === staffId)?.name || `Staff #${staffId}`
}

function resetForm() {
  editingBlock.value = null
  staffSelection.value = ''
  form.starts_at = ''
  form.ends_at = ''
  form.reason = ''
}

function editBlock(block: TimeBlock) {
  editingBlock.value = block
  staffSelection.value = block.staff_user_id ? String(block.staff_user_id) : ''
  form.starts_at = toDateTimeLocal(block.starts_at)
  form.ends_at = toDateTimeLocal(block.ends_at)
  form.reason = block.reason || ''
  successMessage.value = ''
  errorMessage.value = ''
}

async function saveBlock() {
  errorMessage.value = ''
  successMessage.value = ''
  isSaving.value = true

  try {
    const endpoint = editingBlock.value ? `/api/time-blocks/${editingBlock.value.id}` : '/api/time-blocks'
    const request = await useSanctumFetch(endpoint, {
      method: editingBlock.value ? 'PUT' : 'POST',
      body: {
        staff_user_id: staffSelection.value ? Number(staffSelection.value) : null,
        starts_at: form.starts_at,
        ends_at: form.ends_at,
        reason: form.reason || null
      },
      key: `time-block-save-${Date.now()}`,
      server: false,
      watch: false
    })

    if (request.error.value) {
      throw request.error.value
    }

    successMessage.value = editingBlock.value ? 'Block updated.' : 'Block added.'
    resetForm()
    await blocksRequest.refresh()
  } catch (saveError) {
    errorMessage.value = getApiErrorMessage(saveError, 'Block could not be saved.')
  } finally {
    isSaving.value = false
  }
}
</script>
