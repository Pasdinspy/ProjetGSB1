import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  const user = ref(null)
  const isAuthenticated = ref(false)
  const BASE_URL = 'http://51.83.74.206:8000'

  // Computed properties
  const userRole = computed(() => user.value?.role)
  const userId = computed(() => user.value?.id)
  const visiteurId = computed(() => user.value?.VIS_ID)

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

      if (data.succes) {
        setUserData(data.donnees.user)
        return { success: true }
      }
      
      return { 
        success: false, 
        message: data.message || 'Identifiants incorrects'
      }
    } catch (error) {
      console.error('Erreur de connexion:', error)
      return { 
        success: false, 
        message: 'Erreur de connexion au serveur'
      }
    }
  }

  const setUserData = (userData) => {
    user.value = {
      id: userData.id,
      username: userData.username,
      role: userData.role,
      VIS_ID: userData.VIS_ID,
      dteConnexion: new Date().toISOString()
    }
    isAuthenticated.value = true
    saveToStorage()
  }

  const logout = () => {
    user.value = null
    isAuthenticated.value = false
    clearStorage()
    router.push('/login')
  }

  const saveToStorage = () => {
    localStorage.setItem('gsb_user', JSON.stringify(user.value))
    localStorage.setItem('gsb_auth_time', new Date().toISOString())
  }

  const clearStorage = () => {
    localStorage.removeItem('gsb_user')
    localStorage.removeItem('gsb_auth_time')
  }

  const initializeStore = () => {
    const storedUser = localStorage.getItem('gsb_user')
    const authTime = localStorage.getItem('gsb_auth_time')

    if (storedUser && authTime) {
      // Session expire après 24h
      const expirationTime = new Date(authTime).getTime() + (24 * 60 * 60 * 1000)
      if (new Date().getTime() < expirationTime) {
        user.value = JSON.parse(storedUser)
        isAuthenticated.value = true
        return true
      }
      clearStorage()
    }
    return false
  }

  // Helpers pour les rôles
  const checkRole = (allowedRoles) => {
    if (!user.value) return false
    return Array.isArray(allowedRoles) 
      ? allowedRoles.includes(user.value.role)
      : user.value.role === allowedRoles
  }

  return {
    // State
    user,
    isAuthenticated,

    // Computed
    userRole,
    userId,
    visiteurId,

    // Actions
    login,
    logout,
    initializeStore,
    checkRole,

    // Role checks
    isVisiteurMedical: () => checkRole('VISITEUR_MEDICAL'),
    isComptable: () => checkRole('COMPTABLE'),
    isAdministrateur: () => checkRole('ADMINISTRATEUR')
  }
})