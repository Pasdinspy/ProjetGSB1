<!-- gsb-front/src/App.vue -->
<template>
  <div class="app-container">
    <!-- Barre de navigation si l'utilisateur est connecté -->
    <nav v-if="authStore.isAuthenticated" class="main-nav">
      <div class="nav-brand">GSB</div>
      <div class="nav-links">
        <router-link to="/" class="nav-link">
          <i class="fas fa-home"></i> Accueil
        </router-link>
        <router-link to="/frais" class="nav-link" v-if="authStore.isVisiteurMedical()">
          <i class="fas fa-receipt"></i> Frais
        </router-link>
        <router-link to="/historique" class="nav-link">
          <i class="fas fa-history"></i> Historique
        </router-link>
        <button @click="handleLogout" class="logout-button">
          <i class="fas fa-sign-out-alt"></i> Déconnexion
        </button>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'
import { onMounted } from 'vue'

const authStore = useAuthStore()
const router = useRouter()

onMounted(() => {
  authStore.initializeStore()
})

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style>
/* Reset CSS */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-nav {
  background: #1e3c72;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: bold;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.logout-button {
  background: #ff4757;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.3s;
}

.logout-button:hover {
  background: #ff6b81;
}

.main-content {
  flex: 1;
  padding: 2rem;
  background: #f5f7fa;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>