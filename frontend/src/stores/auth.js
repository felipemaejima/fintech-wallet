import { defineStore } from 'pinia'
import api, { TOKEN_KEY } from '@/services/api'

const USER_KEY = 'fintech_user'

function loadStoredUser() {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? JSON.parse(raw) : null
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(TOKEN_KEY) || null,
    user: loadStoredUser(),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setSession(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem(TOKEN_KEY, token)
      localStorage.setItem(USER_KEY, JSON.stringify(user))
    },

    async login(credentials) {
      const { data } = await api.post('/login', credentials)
      this.setSession(data.token, data.user)
    },

    async register(payload) {
      const { data } = await api.post('/register', payload)
      this.setSession(data.token, data.user)
    },

    async logout() {
      try {
        await api.post('/logout')
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem(TOKEN_KEY)
        localStorage.removeItem(USER_KEY)
      }
    },
  },
})
