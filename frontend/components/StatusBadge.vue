<template>
  <span :class="classes">
    {{ label }}
  </span>
</template>

<script setup lang="ts">
const props = defineProps<{
  value: string | boolean | number | null | undefined
}>()

const normalized = computed(() => String(props.value ?? 'unknown').toLowerCase())
const label = computed(() => {
  if (typeof props.value === 'boolean') {
    return props.value ? 'Active' : 'Inactive'
  }

  return String(props.value ?? 'Unknown').replaceAll('_', ' ')
})

const classes = computed(() => {
  const base = 'inline-flex min-h-6 items-center rounded-full border px-2 py-0.5 text-xs font-semibold capitalize'

  if (['booked', 'paid', 'active', 'true'].includes(normalized.value)) {
    return `${base} border-emerald-200 bg-emerald-50 text-emerald-800`
  }

  if (['pending', 'pending_payment'].includes(normalized.value)) {
    return `${base} border-amber-200 bg-amber-50 text-amber-800`
  }

  if (['cancelled', 'canceled', 'inactive', 'false'].includes(normalized.value)) {
    return `${base} border-rose-200 bg-rose-50 text-rose-800`
  }

  return `${base} border-zinc-200 bg-zinc-50 text-zinc-700`
})
</script>
