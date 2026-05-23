<template>
  <div class="min-h-screen bg-zinc-50">
    <header class="border-b border-zinc-200 bg-white">
      <div class="mx-auto flex min-h-16 w-full max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <NuxtLink to="/public/book" class="font-bold text-zinc-950">Appointment SaaS</NuxtLink>
        <div class="flex items-center gap-2">
          <UButton to="/public/book" color="neutral" variant="ghost" icon="i-lucide-calendar-plus">
            Book
          </UButton>
          <UButton to="/login" color="primary" variant="solid" icon="i-lucide-log-in">
            Sign in
          </UButton>
        </div>
      </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>
