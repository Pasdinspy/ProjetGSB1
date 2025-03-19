<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-900">404</h1>
        <p class="text-xl text-gray-600 mt-4">Page non trouvée</p>
        <router-link 
          to="/" 
          class="mt-6 inline-block bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700"
        >
          Retour à l'accueil
        </router-link>
      </div>
    </div>
  </template>