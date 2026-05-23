<template>
  <div class="grid gap-3">
    <UAlert
      v-if="error"
      color="error"
      variant="soft"
      icon="i-lucide-circle-alert"
      title="Unable to save"
      :description="error"
    />
    <UAlert
      v-if="success"
      color="success"
      variant="soft"
      icon="i-lucide-circle-check"
      title="Saved"
      :description="success"
    />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  error?: string
  success?: string
}>()
</script>
