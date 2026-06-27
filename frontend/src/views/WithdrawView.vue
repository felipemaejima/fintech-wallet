<script setup>
import AppLayout from '@/components/AppLayout.vue'
import WalletOperationForm from '@/components/WalletOperationForm.vue'
</script>

<template>
  <AppLayout>
    <WalletOperationForm
      title="Realizar saque"
      endpoint="/wallet/withdraw"
      action-label="Sacar"
    />
  </AppLayout>
</template>
