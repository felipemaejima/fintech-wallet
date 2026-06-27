<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const loggingOut = ref(false)

const links = [
  { name: 'dashboard', label: 'Dashboard' },
  { name: 'deposit', label: 'Depósito' },
  { name: 'withdraw', label: 'Saque' },
  { name: 'history', label: 'Histórico' },
]

async function handleLogout() {
  loggingOut.value = true
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen">
    <header class="bg-white shadow">
      <div class="mx-auto flex max-w-4xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
          <span class="text-lg font-bold text-emerald-600">Fintech Wallet</span>
          <span v-if="auth.user" class="text-sm text-slate-500">Olá, {{ auth.user.name }}</span>
        </div>

        <nav class="flex flex-wrap items-center gap-1">
          <RouterLink
            v-for="link in links"
            :key="link.name"
            :to="{ name: link.name }"
            class="rounded px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100"
            active-class="bg-emerald-50 text-emerald-700"
          >
            {{ link.label }}
          </RouterLink>
          <button
            type="button"
            class="rounded px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-50"
            :disabled="loggingOut"
            @click="handleLogout"
          >
            Sair
          </button>
        </nav>
      </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-6">
      <slot />
    </main>
  </div>
</template>
