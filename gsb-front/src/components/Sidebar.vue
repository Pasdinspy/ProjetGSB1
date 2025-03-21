<template>
    <aside class="sidebar">
      <h2 class="sidebar-title">GSB Menu</h2>
      <nav class="sidebar-nav">
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
      </nav>
    </aside>
  </template>
  
  <script setup>
  import { useAuthStore } from '../stores/auth'
  
  const authStore = useAuthStore()
  </script>
  
  <style scoped>
  .sidebar {
    width: 200px;
    background: #1e3c72;
    color: white;
    padding: 1rem;
    border-radius: 15px;
    margin: 2rem auto;
    transition: all 0.3s ease;
    transform: translateY(0);
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .sidebar-title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    text-align: center;
  }
  
  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
  }
  
  .nav-link {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: background-color 0.3s, transform 0.3s;
  }
  
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
  }
  
  /* Animation for the sidebar */
  .sidebar:hover {
    transform: translateY(-10px);
  }
  </style>