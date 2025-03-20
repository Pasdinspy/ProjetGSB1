<template>
    <div class="error-container">
      <div class="error-content">
        <h1>403</h1>
        <h2>Accès Refusé</h2>
        <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
        <div class="actions">
          <button @click="goBack" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
          </button>
          <button @click="goHome" class="btn-home">
            <i class="fas fa-home"></i> Accueil
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router'
  
  const router = useRouter()
  
  const goBack = () => {
    router.back()
  }
  
  const goHome = () => {
    router.push('/')
  }
  </script>
  
  <style scoped>
  .error-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
  }
  
  .error-content {
    text-align: center;
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }
  
  h1 {
    font-size: 6em;
    color: #ff3e3e;
    margin: 0;
  }
  
  h2 {
    color: #333;
    margin: 10px 0 20px;
  }
  
  p {
    color: #666;
    margin-bottom: 30px;
  }
  
  .actions {
    display: flex;
    gap: 15px;
    justify-content: center;
  }
  
  button {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
  }
  
  .btn-back {
    background: #f8f9fa;
    color: #333;
  }
  
  .btn-home {
    background: #1e3c72;
    color: white;
  }
  
  button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  </style>