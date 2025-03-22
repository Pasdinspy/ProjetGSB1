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