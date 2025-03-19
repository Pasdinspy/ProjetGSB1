import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { 
      path: '/login', 
      name: 'login', 
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '/frais',
          name: 'Frais',
          component: () => import('../views/Frais.vue'),
          meta: { 
            requiresAuth: true,
            roles: ['ADMINISTRATEUR', 'VISITEUR_MEDICAL']
          }
        },
        {
          path: '/historique',
          name: 'Historique',
          component: () => import('../views/Historique.vue'),
          meta: { 
            requiresAuth: true,
            roles: ['ADMINISTRATEUR', 'VISITEUR_MEDICAL']
          }
        },
        {
          path: '/employees',
          name: 'Employees',
          component: () => import('../views/Employees.vue'),
          meta: { 
            requiresAuth: true,
            roles: ['ADMINISTRATEUR']
          },
        },
        {
          path: '/payments',
          name: 'Payments',
          component: () => import('../views/Payments.vue'),
          meta: { 
            requiresAuth: true,
            roles: ['ADMINISTRATEUR', 'COMPTABLE']
          },
        },
      ]
    },
    { 
      path: '/403', 
      name: 'forbidden', 
      component: () => import('../views/403.vue')
    },
    { 
      path: '/:pathMatch(.*)*', 
      name: '404', 
      component: () => import('../views/404.vue') 
    }
  ],
})

// Navigation guard avec Pinia
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  console.log(`Navigation vers: ${to.path}`)

  // Initialiser le store si nécessaire
  if (!authStore.isAuthenticated) {
    authStore.initializeStore()
  }

  // Si la route ne nécessite pas d'authentification
  if (!to.meta.requiresAuth) {
    return next()
  }

  // Vérification de l'authentification
  if (!authStore.checkSession()) {
    console.log("Non authentifié, redirection vers login")
    return next('/login')
  }

  // Vérification des rôles
  if (to.meta.roles) {
    const userRole = authStore.getUserRole()
    if (!to.meta.roles.includes(userRole)) {
      console.log("Accès non autorisé pour ce rôle")
      return next('/403')
    }

    // Gestion spéciale pour les visiteurs médicaux
    if (userRole === 'VISITEUR_MEDICAL' && 
       (to.name === 'Historique' || to.name === 'Frais')) {
      const userId = authStore.getUserId()
      return next({
        ...to,
        query: { ...to.query, userId: userId }
      })
    }
  }

  next()
})

export default router