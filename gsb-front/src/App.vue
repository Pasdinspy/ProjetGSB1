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
        <router-link to="/historique" class="nav-link" v-if="authStore.isVisiteurMedical() || authStore.isAdministrateur()">
          <i class="fas fa-history"></i> Historique
        </router-link>
        <router-link to="/employees" class="nav-link" v-if="authStore.isAdministrateur()">
          <i class="fas fa-users"></i> Employés
        </router-link>
        <router-link to="/payments" class="nav-link" v-if="authStore.isAdministrateur() || authStore.isComptable()">
          <i class="fas fa-money-check-alt"></i> Paiements
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