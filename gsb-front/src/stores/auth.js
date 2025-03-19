import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const isAuthenticated = ref(false)
  const BASE_URL = 'http://51.83.74.206:8000'

  // Stocke la date de dernière connexion
  const lastConnectionTime = ref(null)

  const login = async (username, password) => {
    try {
      const response = await fetch(`${BASE_URL}/src/login.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({ username, password }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log('Réponse du serveur:', data)

      if (data.succes) {
        // Mise à jour des informations utilisateur
        user.value = {
          id: data.donnees.user.id,          // id de la table user
          username: data.donnees.user.username,
          role: data.donnees.user.role,      // VISITEUR_MEDICAL, COMPTABLE, ou ADMINISTRATEUR
          VIS_ID: data.donnees.user.VIS_ID,  // peut être null
          dteConnexion: data.donnees.user.dteConnexion // timestamp de dernière connexion
        }
        
        // Mise à jour de l'authentification
        isAuthenticated.value = true
        lastConnectionTime.value = new Date().toISOString()
        
        // Sauvegarde en localStorage
        localStorage.setItem('user', JSON.stringify(user.value))
        localStorage.setItem('lastConnectionTime', lastConnectionTime.value)
        
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
    lastConnectionTime.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('lastConnectionTime')
  }

  const initializeStore = () => {
    const storedUser = localStorage.getItem('user')
    const storedLastConnection = localStorage.getItem('lastConnectionTime')
    
    if (storedUser) {
      user.value = JSON.parse(storedUser)
      lastConnectionTime.value = storedLastConnection
      isAuthenticated.value = true
    }
  }

  const checkSession = () => {
    return isAuthenticated.value && user.value !== null
  }

  const getUserRole = () => {
    return user.value?.role || null
  }

  const getUserId = () => {
    return user.value?.id || null
  }

  const getVisiteurId = () => {
    return user.value?.VIS_ID || null
  }

  const getLastConnectionTime = () => {
    return lastConnectionTime.value
  }

  const isVisiteurMedical = () => {
    return user.value?.role === 'VISITEUR_MEDICAL'
  }

  const isComptable = () => {
    return user.value?.role === 'COMPTABLE'
  }

  const isAdministrateur = () => {
    return user.value?.role === 'ADMINISTRATEUR'
  }

  return {
    user,
    isAuthenticated,
    lastConnectionTime,
    login,
    logout,
    initializeStore,
    checkSession,
    getUserRole,
    getUserId,
    getVisiteurId,
    getLastConnectionTime,
    isVisiteurMedical,
    isComptable,
    isAdministrateur
  }
})