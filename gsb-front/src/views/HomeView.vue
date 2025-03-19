<template>
    <div class="min-h-screen bg-gray-100">
      <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <div class="flex-shrink-0 flex items-center">
                <h1 class="text-xl font-bold">GSB</h1>
              </div>
              <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                <!-- Navigation links based on user role -->
                <router-link 
                  v-if="['ADMINISTRATEUR', 'VISITEUR_MEDICAL'].includes(authStore.getUserRole())"
                  to="/frais" 
                  class="nav-link"
                >
                  Frais
                </router-link>
                
                <router-link 
                  v-if="['ADMINISTRATEUR', 'VISITEUR_MEDICAL'].includes(authStore.getUserRole())"
                  to="/historique" 
                  class="nav-link"
                >
                  Historique
                </router-link>
                
                <router-link 
                  v-if="authStore.getUserRole() === 'ADMINISTRATEUR'"
                  to="/employees" 
                  class="nav-link"
                >
                  Employés
                </router-link>
                
                <router-link 
                  v-if="['ADMINISTRATEUR', 'COMPTABLE'].includes(authStore.getUserRole())"
                  to="/payments" 
                  class="nav-link"
                >
                  Paiements
                </router-link>
              </div>
            </div>
            <div class="flex items-center">
              <div class="flex items-center space-x-4">
                <span class="text-gray-700">{{ authStore.user?.nom }} {{ authStore.user?.prenom }}</span>
                <button 
                  @click="handleLogout" 
                  class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                >
                  Déconnexion
                </button>
              </div>
            </div>
          </div>
        </div>
      </nav>
  
      <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <router-view></router-view>
        </div>
      </main>
    </div>
  </template>
  
  <script setup>
  import { useAuthStore } from '../stores/auth'
  import { useRouter } from 'vue-router'
  
  const authStore = useAuthStore()
  const router = useRouter()
  
  const handleLogout = () => {
    authStore.logout()
    router.push('/login')
  }
  </script>
  
  <style scoped>
  .nav-link {
    @apply inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-transparent hover:border-gray-300 hover:text-gray-700;
  }
  
  .router-link-active {
    @apply border-indigo-500 text-gray-900;
  }
  </style>