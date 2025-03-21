import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/frais',
      name: 'frais',
      component: () => import('../views/Frais.vue'),
      meta: {
        requiresAuth: true,
        allowedRoles: ['VISITEUR_MEDICAL']
      }
    },
    {
      path: '/historique',
      name: 'historique',
      component: () => import('../views/Historique.vue'),
      meta: {
        requiresAuth: true,
        allowedRoles: ['VISITEUR_MEDICAL', 'ADMINISTRATEUR']
      }
    },
    {
      path: '/employees',
      name: 'employees',
      component: () => import('../views/Employees.vue'),
      meta: {
        requiresAuth: true,
        allowedRoles: ['ADMINISTRATEUR']
      }
    },
    {
      path: '/payments',
      name: 'payments',
      component: () => import('../views/Payments.vue'),
      meta: {
        requiresAuth: true,
        allowedRoles: ['COMPTABLE', 'ADMINISTRATEUR']
      }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Si l'utilisateur va vers login et est déjà connecté
  if (to.path === '/login' && authStore.isAuthenticated) {
    return next('/')
  }

  // Si la route ne nécessite pas d'authentification
  if (!to.meta.requiresAuth) {
    return next()
  }

  // Vérifie si l'utilisateur est authentifié
  if (!authStore.isAuthenticated) {
    authStore.initializeStore()
    
    if (!authStore.isAuthenticated) {
      return next('/login')
    }
  }

  // Vérifie les rôles si nécessaire
  if (to.meta.allowedRoles && !to.meta.allowedRoles.includes(authStore.user.role)) {
    return next('/')
  }

  next()
})

export default router