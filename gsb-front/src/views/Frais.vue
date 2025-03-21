<template>
    <Layout>
      <div class="frais-container">
        <h1>Soumettre une fiche de frais</h1>
        <form @submit.prevent="submitFrais">
          <div class="form-group">
            <label for="month">Mois</label>
            <select id="month" v-model="frais.month" required>
              <option disabled value="">Sélectionnez un mois</option>
              <option value="Janvier">Janvier</option>
              <option value="Février">Février</option>
              <option value="Mars">Mars</option>
              <option value="Avril">Avril</option>
              <option value="Mai">Mai</option>
              <option value="Juin">Juin</option>
              <option value="Juillet">Juillet</option>
              <option value="Août">Août</option>
              <option value="Septembre">Septembre</option>
              <option value="Octobre">Octobre</option>
              <option value="Novembre">Novembre</option>
              <option value="Décembre">Décembre</option>
            </select>
          </div>
          <div class="form-group">
            <label for="year">Année</label>
            <select id="year" v-model="frais.year" required>
              <option disabled value="">Sélectionnez une année</option>
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div class="form-group">
            <label for="repas">Repas</label>
            <input type="number" id="repas" v-model="frais.repas" required />
          </div>
          <div class="form-group">
            <label for="nuitees">Nuitées</label>
            <input type="number" id="nuitees" v-model="frais.nuitees" required />
          </div>
          <div class="form-group">
            <label for="etape">Étape</label>
            <input type="number" id="etape" v-model="frais.etape" required />
          </div>
          <div class="form-group">
            <label for="km">Kilomètres</label>
            <input type="number" id="km" v-model="frais.km" required />
          </div>
          <button type="submit" class="submit-button">Soumettre</button>
        </form>
        <div v-if="message" class="message">{{ message }}</div>
      </div>
    </Layout>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import Layout from '../components/Layout.vue'
  
  const authStore = useAuthStore()
  const frais = ref({
    month: '',
    year: '',
    repas: 0,
    nuitees: 0,
    etape: 0,
    km: 0,
    VIS_ID: authStore.user.VIS_ID
  })
  const message = ref('')
  
  // Generate years from 2000 to current year + 1
  const currentYear = new Date().getFullYear()
  const years = Array.from({ length: currentYear - 1999 + 2 }, (_, i) => 2000 + i)
  
  const submitFrais = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/insertFicheDeFrais.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(frais.value),
      })
  
      const data = await response.json()
      if (data.succes) {
        message.value = 'Fiche de frais soumise avec succès'
      } else {
        message.value = data.message || 'Erreur lors de la soumission de la fiche de frais'
      }
    } catch (error) {
      message.value = 'Erreur de connexion au serveur'
    }
  }
  </script>
  
  <style scoped>
  .frais-container {
    flex: 1;
    margin-left: 200px; /* To make space for the sidebar */
    padding: 2rem;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  
  h1 {
    text-align: center;
    margin-bottom: 2rem;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  label {
    display: block;
    margin-bottom: 0.5rem;
  }
  
  input, select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  
  .submit-button {
    display: block;
    width: 100%;
    padding: 0.75rem;
    background: #1e3c72;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
  }
  
  .submit-button:hover {
    background: #2a5298;
  }
  
  .message {
    margin-top: 1rem;
    text-align: center;
    color: green;
  }
  </style>