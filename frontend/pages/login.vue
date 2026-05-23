<template>
  <div class="mx-auto grid min-h-[calc(100vh-8rem)] w-full max-w-5xl items-center gap-8 lg:grid-cols-[1fr_440px]">
    <section class="hidden lg:block">
      <p class="text-sm font-semibold text-teal-700">Business workspace</p>
      <h1 class="mt-3 max-w-xl text-4xl font-bold tracking-normal text-zinc-950">Run the day from one appointment desk.</h1>
      <div class="mt-8 grid max-w-xl gap-3">
        <div class="panel flex items-center gap-3 p-4">
          <UIcon name="i-lucide-calendar-check" class="size-5 text-teal-700" />
          <span class="text-sm font-semibold text-zinc-800">Booking, customers, services, and blocks stay together.</span>
        </div>
        <div class="panel flex items-center gap-3 p-4">
          <UIcon name="i-lucide-credit-card" class="size-5 text-indigo-700" />
          <span class="text-sm font-semibold text-zinc-800">Square checkout remains part of every public booking.</span>
        </div>
      </div>
    </section>

    <section class="panel p-5 sm:p-6">
      <div class="mb-5 grid grid-cols-2 gap-2 rounded-md bg-zinc-100 p-1">
        <button
          class="min-h-10 rounded-md text-sm font-semibold transition"
          :class="mode === 'login' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-600'"
          type="button"
          @click="mode = 'login'"
        >
          Login
        </button>
        <button
          class="min-h-10 rounded-md text-sm font-semibold transition"
          :class="mode === 'register' ? 'bg-white text-zinc-950 shadow-sm' : 'text-zinc-600'"
          type="button"
          @click="mode = 'register'"
        >
          Register
        </button>
      </div>

      <ApiFeedback class="mb-4" :error="errorMessage" :success="successMessage" />

      <form v-if="mode === 'login'" class="grid gap-4" @submit.prevent="submitLogin">
        <label class="field">
          <span class="label">Email</span>
          <input v-model="loginForm.email" class="input" type="email" autocomplete="email" required>
        </label>
        <label class="field">
          <span class="label">Password</span>
          <input v-model="loginForm.password" class="input" type="password" autocomplete="current-password" required>
        </label>
        <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700">
          <input v-model="loginForm.remember" class="size-4 rounded border-zinc-300" type="checkbox">
          Remember me
        </label>
        <UButton type="submit" color="primary" block icon="i-lucide-log-in" :loading="isSubmitting">
          Sign in
        </UButton>
      </form>

      <form v-else class="grid gap-4" @submit.prevent="submitRegister">
        <label class="field">
          <span class="label">Your name</span>
          <input v-model="registerForm.name" class="input" autocomplete="name" required>
        </label>
        <label class="field">
          <span class="label">Business name</span>
          <input v-model="registerForm.business_name" class="input" required>
        </label>
        <label class="field">
          <span class="label">Email</span>
          <input v-model="registerForm.email" class="input" type="email" autocomplete="email" required>
        </label>
        <label class="field">
          <span class="label">Password</span>
          <input v-model="registerForm.password" class="input" type="password" autocomplete="new-password" minlength="8" required>
        </label>
        <label class="field">
          <span class="label">Confirm password</span>
          <input v-model="registerForm.password_confirmation" class="input" type="password" autocomplete="new-password" minlength="8" required>
        </label>
        <UButton type="submit" color="primary" block icon="i-lucide-user-plus" :loading="isSubmitting">
          Create workspace
        </UButton>
      </form>
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'public',
  middleware: ['sanctum:guest']
})

const { login } = useSanctumAuth()
const mode = ref<'login' | 'register'>('login')
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const loginForm = reactive({
  email: '',
  password: '',
  remember: true
})

const registerForm = reactive({
  name: '',
  business_name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

async function submitLogin() {
  errorMessage.value = ''
  successMessage.value = ''
  isSubmitting.value = true

  try {
    await login({ ...loginForm })
    await navigateTo('/dashboard')
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Check your credentials and try again.')
  } finally {
    isSubmitting.value = false
  }
}

async function submitRegister() {
  errorMessage.value = ''
  successMessage.value = ''

  if (registerForm.password !== registerForm.password_confirmation) {
    errorMessage.value = 'Passwords must match.'
    return
  }

  isSubmitting.value = true

  try {
    const { error } = await useSanctumFetch('/register', {
      method: 'POST',
      body: { ...registerForm },
      key: `register-${Date.now()}`,
      server: false,
      watch: false
    })

    if (error.value) {
      throw error.value
    }

    await login({
      email: registerForm.email,
      password: registerForm.password,
      remember: true
    })

    await navigateTo('/dashboard')
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, 'Registration could not be completed.')
  } finally {
    isSubmitting.value = false
  }
}
</script>
