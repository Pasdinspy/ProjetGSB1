<template>
    <div class="home-container">
      <div class="welcome-card" v-if="authStore.user">
        <div class="profile-header">
          <div class="avatar">
            {{ getInitials() }}
          </div>
          <h1>Bienvenue, {{ authStore.user.username }} !</h1>
        </div>
  
        <div class="info-grid">
          <!-- Carte Rôle -->
          <div class="info-card role">
            <div class="icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <div class="info-content">
              <h3>Rôle</h3>
              <p>{{ formatRole(authStore.user.role) }}</p>
            </div>
          </div>
  
          <!-- Carte ID Visiteur -->
          <div class="info-card visitor" v-if="authStore.user.VIS_ID">
            <div class="icon">
              <i class="fas fa-id-badge"></i>
            </div>
            <div class="info-content">
              <h3>ID Visiteur</h3>
              <p>{{ authStore.user.VIS_ID }}</p>
            </div>
          </div>
  
          <!-- Carte Dernière Connexion -->
          <div class="info-card connection">
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="info-content">
              <h3>Dernière Connexion</h3>
              <p>{{ formatDate(authStore.user.dteConnexion) }}</p>
            </div>
          </div>
        </div>
  
        <div class="action-buttons">
          <button @click="handleLogout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Se déconnecter
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  import { onMounted } from 'vue'
  
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
  
  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
  
  const handleLogout = () => {
    authStore.logout()
    router.push('/login')
  }
  </script>
  
  <style scoped>
  .home-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .welcome-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    width: 100%;
    max-width: 800px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.6s ease-out;
  }
  
  .profile-header {
    text-align: center;
    margin-bottom: 40px;
    animation: fadeIn 0.8s ease-out;
  }
  
  .avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 2.5em;
    font-weight: bold;
    margin: 0 auto 20px;
    box-shadow: 0 5px 15px rgba(46, 76, 144, 0.3);
  }
  
  h1 {
    color: #2a5298;
    font-size: 2em;
    margin: 0;
  }
  
  .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }
  
  .info-card {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: slideUp 0.6s ease-out;
  }
  
  .info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  
  .icon {
    width: 50px;
    height: 50px;
    background: #1e3c72;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 15px;
  }
  
  .icon i {
    color: white;
    font-size: 1.5em;
  }
  
  .info-content {
    flex: 1;
  }
  
  .info-content h3 {
    margin: 0;
    color: #1e3c72;
    font-size: 1.1em;
    margin-bottom: 5px;
  }
  
  .info-content p {
    margin: 0;
    color: #666;
    font-size: 1.2em;
    font-weight: 500;
  }
  
  .action-buttons {
    text-align: center;
    margin-top: 30px;
  }
  
  .logout-btn {
    background: #ff4757;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 25px;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .logout-btn:hover {
    background: #ff6b81;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(-20px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  
  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
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
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .welcome-card {
      padding: 20px;
    }
  
    .info-grid {
      grid-template-columns: 1fr;
    }
  
    h1 {
      font-size: 1.5em;
    }
  
    .avatar {
      width: 80px;
      height: 80px;
      font-size: 2em;
    }
  }
  </style>