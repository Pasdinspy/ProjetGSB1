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
  
  <style scoped>
  .login-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    padding: 20px;
  }
  
  .login-box {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(10px);
    transform: translateY(20px);
    animation: slideUp 0.6s ease forwards;
  }
  
  .login-header {
    text-align: center;
    margin-bottom: 40px;
  }
  
  .logo {
    font-size: 3em;
    font-weight: bold;
    color: #1e3c72;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
  }
  
  h1 {
    color: #2a5298;
    font-size: 1.8em;
    margin: 0;
    font-weight: 600;
  }
  
  .form-group {
    position: relative;
    margin-bottom: 30px;
  }
  
  input {
    width: 100%;
    padding: 10px 0;
    font-size: 16px;
    color: #333;
    border: none;
    border-bottom: 2px solid #ddd;
    outline: none;
    background: transparent;
    transition: 0.3s;
  }
  
  label {
    position: absolute;
    top: 0;
    left: 0;
    padding: 10px 0;
    font-size: 16px;
    color: #666;
    transition: 0.3s;
    pointer-events: none;
  }
  
  .line {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #1e3c72;
    transform: scaleX(0);
    transition: 0.3s;
  }
  
  input:focus ~ label,
  input.has-value ~ label {
    top: -20px;
    font-size: 12px;
    color: #1e3c72;
  }
  
  input:focus ~ .line,
  input.has-value ~ .line {
    transform: scaleX(1);
  }
  
  .login-button {
    width: 100%;
    padding: 12px;
    background: #1e3c72;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    position: relative;
    overflow: hidden;
  }
  
  .login-button:hover {
    background: #2a5298;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46, 76, 144, 0.4);
  }
  
  .login-button:disabled {
    background: #ccc;
    cursor: not-allowed;
  }
  
  .toggle-password {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 5px;
  }
  
  .error-message {
    margin-top: 20px;
    color: #ff3e3e;
    font-size: 14px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    animation: shake 0.5s ease-in-out;
  }
  
  .loader {
    width: 20px;
    height: 20px;
    border: 3px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin: 0 auto;
  }
  
  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes pulse {
    0% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
    100% {
      transform: scale(1);
    }
  }
  
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
  }
  
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
  
  /* Animation pour le focus des inputs */
  input:focus {
    animation: glow 1s ease-in-out infinite alternate;
  }
  
  @keyframes glow {
    from {
      box-shadow: 0 0 5px rgba(30, 60, 114, 0.2);
    }
    to {
      box-shadow: 0 0 10px rgba(30, 60, 114, 0.4);
    }
  }
  
  /* Responsive design */
  @media (max-width: 480px) {
    .login-box {
      padding: 20px;
    }
  
    .logo {
      font-size: 2.5em;
    }
  
    h1 {
      font-size: 1.5em;
    }
  }
  </style>