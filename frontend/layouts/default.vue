<template>
  <div class="min-h-screen bg-zinc-50">
    <aside class="fixed inset-y-0 left-0 hidden w-64 border-r border-zinc-200 bg-white lg:block">
      <div class="flex h-full flex-col">
        <div class="border-b border-zinc-200 p-5">
          <NuxtLink to="/dashboard" class="block">
            <p class="text-lg font-bold text-zinc-950">Appointment SaaS</p>
            <p class="text-sm text-zinc-500">{{ businessName }}</p>
          </NuxtLink>
        </div>

        <nav class="flex-1 space-y-1 p-3">
          <NuxtLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="flex min-h-10 items-center gap-3 rounded-md px-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
            :class="{ 'bg-teal-50 text-teal-800': route.path === item.to || route.path.startsWith(`${item.to}/`) }"
          >
            <UIcon :name="item.icon" class="size-4" />
            {{ item.label }}
          </NuxtLink>
        </nav>

        <div class="border-t border-zinc-200 p-3">
          <UButton block color="neutral" variant="ghost" icon="i-lucide-log-out" :loading="loggingOut" @click="signOut">
            Sign out
          </UButton>
        </div>
      </div>
    </aside>

    <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white lg:hidden">
      <div class="flex min-h-16 items-center justify-between px-4">
        <NuxtLink to="/dashboard" class="font-bold text-zinc-950">Appointment SaaS</NuxtLink>
        <UButton color="neutral" variant="ghost" icon="i-lucide-log-out" :loading="loggingOut" @click="signOut" />
      </div>
      <nav class="flex gap-1 overflow-x-auto border-t border-zinc-100 px-3 py-2">
        <NuxtLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="inline-flex min-h-9 shrink-0 items-center gap-2 rounded-md px-3 text-sm font-semibold text-zinc-700"
          :class="{ 'bg-teal-50 text-teal-800': route.path === item.to || route.path.startsWith(`${item.to}/`) }"
        >
          <UIcon :name="item.icon" class="size-4" />
          {{ item.label }}
        </NuxtLink>
      </nav>
    </header>

    <main class="lg:pl-64">
      <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const { user, logout } = useSanctumAuth()
const loggingOut = ref(false)

const businessName = computed(() => {
  const identity = user.value as { name?: string } | null
  return identity?.name || 'Workspace'
})

const navItems = [
  { label: 'Dashboard', to: '/dashboard', icon: 'i-lucide-layout-dashboard' },
  { label: 'Business', to: '/business', icon: 'i-lucide-building-2' },
  { label: 'Staff', to: '/staff', icon: 'i-lucide-users' },
  { label: 'Services', to: '/services', icon: 'i-lucide-briefcase-business' },
  { label: 'Appointments', to: '/appointments', icon: 'i-lucide-calendar-days' },
  { label: 'Customers', to: '/customers', icon: 'i-lucide-address-book' },
  { label: 'Settings', to: '/settings', icon: 'i-lucide-settings' }
]

async function signOut() {
  loggingOut.value = true

  try {
    await logout()
  } finally {
    loggingOut.value = false
    await navigateTo('/login')
  }
}
</script>
