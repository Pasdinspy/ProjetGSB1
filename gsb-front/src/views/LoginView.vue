<template>
  <div class="login-container">
    <div class="login-box">
      <!-- Logo et titre -->
      <div class="login-header">
        <div class="logo">GSB</div>
        <h1>Connexion</h1>
      </div>

      <!-- Formulaire -->
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <input 
            v-model="username"
            type="text"
            required
            :class="{ 'has-value': username }"
          >
          <label>Nom d'utilisateur</label>
          <span class="line"></span>
        </div>

        <div class="form-group">
          <input 
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            required
            :class="{ 'has-value': password }"
          >
          <label>Mot de passe</label>
          <span class="line"></span>
          <button 
            type="button" 
            class="toggle-password"
            @click="showPassword = !showPassword"
          >
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </button>
        </div>

        <button 
          type="submit" 
          class="login-button"
          :disabled="isLoading"
        >
          <span v-if="!isLoading">Se connecter</span>
          <div v-else class="loader"></div>
        </button>
      </form>

      <!-- Message d'erreur -->
      <div v-if="error" class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const error = ref('')

const handleLogin = async () => {
try {
  isLoading.value = true
  error.value = ''

  const result = await authStore.login(username.value, password.value)

  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message || 'Erreur de connexion'
  }
} catch (e) {
  error.value = 'Une erreur est survenue'
} finally {
  isLoading.value = false
}
}
</script>