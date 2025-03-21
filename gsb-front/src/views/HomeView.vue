<template>
  <Layout>
    <main class="main-content">
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

<style scoped>
.main-content {
  flex: 1;
}

.welcome-card {
  background: white;
  border-radius: 15px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.profile-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #eee;
}

.avatar {
  position: relative;
  width: 100px;
  height: 100px;
  background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.initials {
  color: white;
  font-size: 2.5em;
  font-weight: bold;
}

.status-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
}

.role-visitor { background: #2ecc71; }
.role-accountant { background: #3498db; }
.role-admin { background: #e74c3c; }

.user-info h1 {
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.role {
  color: #7f8c8d;
  font-size: 1.1em;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.stat-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-card i {
  font-size: 1.5em;
  color: #1e3c72;
}

.stat-info h3 {
  color: #7f8c8d;
  font-size: 0.9em;
  margin-bottom: 0.3rem;
}

.stat-info p {
  color: #2c3e50;
  font-size: 1.1em;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-section {
    flex-direction: column;
    text-align: center;
  }

  .user-info {
    text-align: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>