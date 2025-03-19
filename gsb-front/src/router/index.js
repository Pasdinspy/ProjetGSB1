import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), },
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
          meta: { requiresAuth: true }
        },
        {
          path: '/historique',
          name: 'Historique',
          component: () => import('../views/Historique.vue'),
          meta: { requiresAuth: true }
        },
        {
          path: '/employees',
          name: 'Employees',
          component: () => import('../views/Employees.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: '/payments',
          name: 'Payments',
          component: () => import('../views/Payments.vue'),
          meta: { requiresAuth: true },
        },
      ]
    },
    { path: '/:pathMatch(.*)*', name: '404', component: () => import('../views/404.vue') }
  ],
});

// 🔹 Vérification de session avant chaque changement de route
router.beforeEach(async (to, from, next) => {
  console.log(`Navigation vers: ${to.path} (auth requise: ${to.meta.requiresAuth ? 'oui' : 'non'})`);

  if (to.meta.requiresAuth) {
    console.log("Vérification de l'authentification...");
    const isAuthenticated = await checkSession();
    console.log("Authentifié:", isAuthenticated);

    if (!isAuthenticated) {
      console.log("Non authentifié, redirection vers la page d'accueil");
      return next('/login');
    }
    console.log("Authentifié, accès autorisé");
  }
  next();
});

export default router;