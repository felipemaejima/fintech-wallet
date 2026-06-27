<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const generalError = ref('')
const loading = ref(false)

async function handleSubmit() {
  loading.value = true
  errors.value = {}
  generalError.value = ''

  try {
    await auth.register(form)
    router.push({ name: 'dashboard' })
  } catch (error) {
    const response = error.response
    if (response?.status === 422 && response.data.errors) {
      errors.value = response.data.errors
    } else {
      generalError.value =
        response?.data?.message || 'Não foi possível cadastrar. Tente novamente.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow">
      <h1 class="mb-1 text-center text-2xl font-bold text-emerald-600">Fintech Wallet</h1>
      <p class="mb-6 text-center text-sm text-slate-500">Crie sua conta</p>

      <p v-if="generalError" class="mb-4 rounded bg-red-50 px-3 py-2 text-sm text-red-600">
        {{ generalError }}
      </p>

      <form class="space-y-4" @submit.prevent="handleSubmit">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Nome</label>
          <input
            v-model="form.name"
            type="text"
            autocomplete="name"
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
          />
          <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name[0] }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">E-mail</label>
          <input
            v-model="form.email"
            type="email"
            autocomplete="email"
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
          />
          <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email[0] }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Senha</label>
          <input
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
          />
          <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password[0] }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar senha</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
          />
        </div>

        <button
          type="submit"
          class="w-full rounded bg-emerald-600 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
          :disabled="loading"
        >
          {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-slate-500">
        Já tem conta?
        <RouterLink :to="{ name: 'login' }" class="font-medium text-emerald-600 hover:underline">
          Entrar
        </RouterLink>
      </p>
    </div>
  </div>
</template>
