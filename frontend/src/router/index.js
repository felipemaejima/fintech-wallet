import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import DashboardView from '@/views/DashboardView.vue'
import DepositView from '@/views/DepositView.vue'
import WithdrawView from '@/views/WithdrawView.vue'
import HistoryView from '@/views/HistoryView.vue'

const routes = [

  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: RegisterView, meta: { guestOnly: true } },

  { path: '/', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/deposit', name: 'deposit', component: DepositView, meta: { requiresAuth: true } },
  { path: '/withdraw', name: 'withdraw', component: WithdrawView, meta: { requiresAuth: true } },
  { path: '/history', name: 'history', component: HistoryView, meta: { requiresAuth: true } },

  { path: '/:pathMatch(.*)*', redirect: { name: 'dashboard' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }
})

export default router
