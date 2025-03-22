<template>
  <Layout>
    <main class="home-main-content">
      <div class="welcome-card">
        <div class="profile-section">
          <div class="avatar">
            <span class="initials">{{ getInitials() }}</span>
            <div class="status-badge" :class="roleClass"></div>
          </div>
          <div class="user-info">
            <h1>Bienvenue, {{ authStore.user.username }} !</h1>
            <p class="role">{{ formatRole(authStore.user.role) }}</p>
          </div>
        </div>
        <div class="stats-grid">
          <div class="stat-card" v-if="authStore.user.VIS_ID">
            <i class="fas fa-id-badge"></i>
            <div class="stat-info">
              <h3>ID Visiteur</h3>
              <p>{{ authStore.user.VIS_ID }}</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </Layout>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { onMounted, computed } from 'vue'
import Layout from '../components/Layout.vue'

const authStore = useAuthStore()
const router = useRouter()

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
})

const getInitials = () => {
  return authStore.user.username.substring(0, 2).toUpperCase()
}

const formatRole = (role) => {
  const roleMap = {
    'VISITEUR_MEDICAL': 'Visiteur Médical',
    'COMPTABLE': 'Comptable',
    'ADMINISTRATEUR': 'Administrateur'
  }
  return roleMap[role] || role
}

const roleClass = computed(() => {
  const roles = {
    'VISITEUR_MEDICAL': 'role-visitor',
    'COMPTABLE': 'role-accountant',
    'ADMINISTRATEUR': 'role-admin'
  }
  return roles[authStore.user.role]
})
</script>