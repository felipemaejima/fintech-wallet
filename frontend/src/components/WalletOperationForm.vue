<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { formatMoney } from '@/utils/format'

const props = defineProps({
  title: { type: String, required: true },
  endpoint: { type: String, required: true },
  actionLabel: { type: String, required: true },
})

const amount = ref('')
const errors = ref({})
const generalError = ref('')
const success = ref(null)
const loading = ref(false)

async function handleSubmit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''
  success.value = null

  try {
    const { data } = await api.post(props.endpoint, { amount: amount.value })
    success.value = { message: data.message, balance: data.balance }
    amount.value = ''
  } catch (error) {
    const response = error.response
    if (response?.status === 422 && response.data.errors) {
      errors.value = response.data.errors
    } else {
      generalError.value =
        response?.data?.message || 'Não foi possível concluir a operação.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-md rounded-lg bg-white p-6 shadow">
    <h1 class="mb-4 text-lg font-semibold text-slate-700">{{ title }}</h1>

    <p
      v-if="success"
      class="mb-4 rounded bg-emerald-50 px-3 py-2 text-sm text-emerald-700"
    >
      {{ success.message }} Novo saldo: {{ formatMoney(success.balance) }}
    </p>

    <p v-if="generalError" class="mb-4 rounded bg-red-50 px-3 py-2 text-sm text-red-600">
      {{ generalError }}
    </p>

    <form class="space-y-4" @submit.prevent="handleSubmit">
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Valor (R$)</label>
        <input
          v-model="amount"
          type="number"
          step="0.01"
          min="0.01"
          placeholder="0,00"
          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
        />
        <p v-if="errors.amount" class="mt-1 text-xs text-red-600">{{ errors.amount[0] }}</p>
      </div>

      <button
        type="submit"
        class="w-full rounded bg-emerald-600 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
        :disabled="loading"
      >
        {{ loading ? 'Processando...' : actionLabel }}
      </button>
    </form>
  </div>
</template>
