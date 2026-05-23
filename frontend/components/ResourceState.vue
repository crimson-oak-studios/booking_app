<template>
  <div v-if="loading" class="panel grid min-h-32 place-items-center p-6 text-sm text-zinc-600">
    Loading
  </div>
  <UAlert
    v-else-if="error"
    color="error"
    variant="soft"
    icon="i-lucide-circle-alert"
    title="Unable to load"
    :description="error"
  />
  <div v-else-if="empty" class="panel grid min-h-32 place-items-center p-6 text-center">
    <div>
      <p class="font-semibold text-zinc-900">{{ emptyTitle || 'Nothing here yet' }}</p>
      <p v-if="emptyText" class="mt-1 text-sm text-zinc-600">{{ emptyText }}</p>
    </div>
  </div>
  <slot v-else />
</template>

<script setup lang="ts">
defineProps<{
  loading?: boolean
  error?: string
  empty?: boolean
  emptyTitle?: string
  emptyText?: string
}>()
</script>
