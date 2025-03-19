// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isAuthenticated = ref(false)
  const BASE_URL = 'http://51.83.74.206:8000' // Ajustez selon votre configuration

  const login = async (username, password) => {
    try {
      const response = await fetch(`${BASE_URL}/gsb-back/src/login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include', // Important pour les cookies
        body: JSON.stringify({ username, password }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log('Réponse du serveur:', data) // Pour le débogage

      if (data.succes) {
        user.value = {
          id: data.donnees.user.id,
          username,
          role: data.donnees.user.role,
          VIS_ID: data.donnees.user.VIS_ID,
          nom: data.donnees.user.VIS_NOM,
          prenom: data.donnees.user.VIS_PRENOM
        }
        isAuthenticated.value = true
        localStorage.setItem('user', JSON.stringify(user.value))
        return { success: true }
      } else {
        return { 
          success: false, 
          message: data.message || 'Identifiants incorrects'
        }
      }
    } catch (error) {
      console.error('Erreur de connexion:', error)
      return { 
        success: false, 
        message: 'Erreur de connexion au serveur'
      }
    }
  }

  const logout = () => {
    user.value = null
    isAuthenticated.value = false
    localStorage.removeItem('user')
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    initializeStore,
    checkSession,
    getUserRole,
    getUserId
  }
})