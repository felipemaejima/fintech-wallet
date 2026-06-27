<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import AppLayout from '@/components/AppLayout.vue'
import TransactionList from '@/components/TransactionList.vue'
import { formatMoney } from '@/utils/format'

const data = ref(null)
const loading = ref(true)
const error = ref('')

async function loadDashboard() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/dashboard')
    data.value = response.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao carregar o dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<template>
  <AppLayout>
    <p v-if="loading" class="text-sm text-slate-500">Carregando...</p>
    <p v-else-if="error" class="rounded bg-red-50 px-3 py-2 text-sm text-red-600">{{ error }}</p>

    <div v-else class="space-y-6">
      <!-- Saldo atual -->
      <section class="rounded-lg bg-emerald-600 p-6 text-white shadow">
        <p class="text-sm opacity-90">Saldo atual</p>
        <p class="mt-1 text-3xl font-bold">{{ formatMoney(data.balance) }}</p>

        <div class="mt-4 flex gap-2">
          <RouterLink
            :to="{ name: 'deposit' }"
            class="rounded bg-white/20 px-4 py-1.5 text-sm font-medium hover:bg-white/30"
          >
            Depositar
          </RouterLink>
          <RouterLink
            :to="{ name: 'withdraw' }"
            class="rounded bg-white/20 px-4 py-1.5 text-sm font-medium hover:bg-white/30"
          >
            Sacar
          </RouterLink>
        </div>
      </section>

      <!-- Totais do mês -->
      <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-lg bg-white p-4 shadow">
          <p class="text-sm text-slate-500">Depositado no mês</p>
          <p class="mt-1 text-xl font-semibold text-emerald-600">
            {{ formatMoney(data.month_deposited) }}
          </p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
          <p class="text-sm text-slate-500">Sacado no mês</p>
          <p class="mt-1 text-xl font-semibold text-red-600">
            {{ formatMoney(data.month_withdrawn) }}
          </p>
        </div>
      </section>

      <!-- Últimas transações -->
      <section class="rounded-lg bg-white p-4 shadow">
        <div class="mb-2 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-slate-700">Últimas transações</h2>
          <RouterLink :to="{ name: 'history' }" class="text-xs text-emerald-600 hover:underline">
            Ver tudo
          </RouterLink>
        </div>
        <TransactionList :transactions="data.recent_transactions" />
      </section>
    </div>
  </AppLayout>
</template>
