# Appointment SaaS Frontend

Nuxt frontend for the appointment management SaaS MVP. The Laravel backend remains at the repository root.

## Implementation notes

- Frontend dependencies are declared in `frontend/package.json`:
  - `nuxt`: `^4.4.6`
  - `@nuxt/ui`: `^4.8.0`
  - `nuxt-auth-sanctum`: `^2.3.4`
- Auth is implemented with `nuxt-auth-sanctum`, route middleware (`sanctum:auth` / `sanctum:guest`), and `useSanctumFetch()` for API calls.

## Setup

Use Node `^22.12.0`, `^24.11.0`, or `>=26.0.0` for Nuxt 4.

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

By default the frontend expects Laravel at `http://localhost:8000`.

## Runtime config

Set these values in `frontend/.env` when your backend or frontend ports differ:

```bash
NUXT_PUBLIC_SANCTUM_BASE_URL=http://localhost:8000
NUXT_SANCTUM_BASE_URL=http://localhost:8000
NUXT_PUBLIC_SANCTUM_ORIGIN=http://localhost:3000
NUXT_SANCTUM_ORIGIN=http://localhost:3000
```

Laravel Sanctum also needs the Nuxt origin in `SANCTUM_STATEFUL_DOMAINS`, for example `localhost:3000`.

## Commands

```bash
npm run dev
npm run build
npm run typecheck
npm run preview
```
