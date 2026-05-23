export default defineNuxtConfig({
  modules: ['@nuxt/ui', 'nuxt-auth-sanctum'],
  ssr: false,
  compatibilityDate: '2026-05-22',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],
  fonts: {
    providers: {
      adobe: false,
      bunny: false,
      fontshare: false,
      fontsource: false,
      google: false,
      googleicons: false
    }
  },
  app: {
    head: {
      title: 'Appointment SaaS',
      meta: [
        {
          name: 'description',
          content: 'Appointment management SaaS MVP'
        }
      ]
    }
  },
  runtimeConfig: {
    sanctum: {
      baseUrl: process.env.NUXT_SANCTUM_BASE_URL || 'http://localhost:8000'
    },
    public: {
      sanctum: {
        baseUrl:
          process.env.NUXT_PUBLIC_SANCTUM_BASE_URL ||
          process.env.NUXT_SANCTUM_BASE_URL ||
          'http://localhost:8000'
      }
    }
  },
  sanctum: {
    mode: 'cookie',
    baseUrl: process.env.NUXT_SANCTUM_BASE_URL || 'http://localhost:8000',
    origin: process.env.NUXT_SANCTUM_ORIGIN || process.env.NUXT_PUBLIC_SANCTUM_ORIGIN || 'http://localhost:3000',
    endpoints: {
      csrf: '/sanctum/csrf-cookie',
      login: '/login',
      logout: '/logout',
      user: '/api/business-profile'
    },
    redirectIfAuthenticated: true,
    redirectIfUnauthenticated: true,
    redirect: {
      onLogin: '/dashboard',
      onLogout: '/login',
      onAuthOnly: '/login',
      onGuestOnly: '/dashboard'
    },
    client: {
      initialRequest: true,
      retry: false
    },
    globalMiddleware: {
      enabled: false
    }
  }
})
