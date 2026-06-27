<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import AppLayout from '@/components/AppLayout.vue'
import TransactionList from '@/components/TransactionList.vue'

const filters = reactive({
  type: '',
  start_date: '',
  end_date: '',
})

const page = ref(1)
const result = ref(null)
const loading = ref(false)
const error = ref('')

async function loadTransactions() {
  loading.value = true
  error.value = ''

  const params = { page: page.value, per_page: 10 }
  if (filters.type) {
    params.type = filters.type
  }
  if (filters.start_date) {
    params.start_date = filters.start_date
  }
  if (filters.end_date) {
    params.end_date = filters.end_date
  }

  try {
    const { data } = await api.get('/transactions', { params })
    result.value = data
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao carregar o histórico.'
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  page.value = 1
  loadTransactions()
}

function changePage(newPage) {
  page.value = newPage
  loadTransactions()
}

onMounted(loadTransactions)
</script>

<template>
  <AppLayout>
    <div class="space-y-4">
      <h1 class="text-lg font-semibold text-slate-700">Histórico de transações</h1>

      <!-- Filtros -->
      <form
        class="grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4"
        @submit.prevent="applyFilters"
      >
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600">Tipo</label>
          <select
            v-model="filters.type"
            class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
          >
            <option value="">Todos</option>
            <option value="credit">Depósito</option>
            <option value="debit">Saque</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600">De</label>
          <input
            v-model="filters.start_date"
            type="date"
            class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-600">Até</label>
          <input
            v-model="filters.end_date"
            type="date"
            class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
          />
        </div>
        <div class="flex items-end">
          <button
            type="submit"
            class="w-full rounded bg-emerald-600 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700"
          >
            Filtrar
          </button>
        </div>
      </form>

      <!-- Lista -->
      <div class="rounded-lg bg-white p-4 shadow">
        <p v-if="loading" class="py-6 text-center text-sm text-slate-500">Carregando...</p>
        <p v-else-if="error" class="rounded bg-red-50 px-3 py-2 text-sm text-red-600">{{ error }}</p>

        <template v-else-if="result">
          <TransactionList :transactions="result.data" />

          <!-- Paginação -->
          <div
            v-if="result.total > 0"
            class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm"
          >
            <span class="text-slate-500">
              Página {{ result.current_page }} de {{ result.last_page }}
              ({{ result.total }} no total)
            </span>
            <div class="flex gap-2">
              <button
                type="button"
                class="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
                :disabled="result.current_page <= 1"
                @click="changePage(result.current_page - 1)"
              >
                Anterior
              </button>
              <button
                type="button"
                class="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
                :disabled="result.current_page >= result.last_page"
                @click="changePage(result.current_page + 1)"
              >
                Próxima
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </AppLayout>
</template>
