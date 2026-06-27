<script setup>
import AppLayout from '@/components/AppLayout.vue'
import WalletOperationForm from '@/components/WalletOperationForm.vue'
</script>

<template>
  <AppLayout>
    <WalletOperationForm
      title="Realizar depósito"
      endpoint="/wallet/deposit"
      action-label="Depositar"
    />
  </AppLayout>
</template>
