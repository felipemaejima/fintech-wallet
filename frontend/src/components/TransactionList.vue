<script setup>
import { formatMoney, formatDate } from '@/utils/format'

defineProps({
  transactions: {
    type: Array,
    required: true,
  },
})

const typeLabel = (type) => (type === 'credit' ? 'Depósito' : 'Saque')
</script>

<template>
  <ul v-if="transactions.length" class="divide-y divide-slate-100">
    <li
      v-for="tx in transactions"
      :key="tx.id"
      class="flex items-center justify-between gap-3 py-3"
    >
      <div>
        <p class="text-sm font-medium text-slate-700">{{ typeLabel(tx.type) }}</p>
        <p class="text-xs text-slate-400">{{ formatDate(tx.created_at) }}</p>
      </div>
      <div class="text-right">
        <p
          class="text-sm font-semibold"
          :class="tx.type === 'credit' ? 'text-emerald-600' : 'text-red-600'"
        >
          {{ tx.type === 'credit' ? '+' : '-' }} {{ formatMoney(tx.amount) }}
        </p>
        <p class="text-xs text-slate-400">Saldo: {{ formatMoney(tx.balance_after) }}</p>
      </div>
    </li>
  </ul>

  <p v-else class="py-6 text-center text-sm text-slate-400">Nenhuma transação encontrada.</p>
</template>
