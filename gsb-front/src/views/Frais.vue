<template>
  <Layout>
    <div class="frais-container">
      <h1>Soumettre une fiche de frais</h1>
      <!-- Message d'erreur si une fiche existe déjà -->
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
      <form v-else @submit.prevent="submitFrais">
        <div class="form-group">
          <label for="month">Mois</label>
          <input 
            type="text" 
            id="month" 
            :value="currentMonth" 
            disabled 
            class="disabled-input"
          />
        </div>
        <div class="form-group">
          <label for="year">Année</label>
          <input 
            type="text" 
            id="year" 
            :value="currentYear" 
            disabled 
            class="disabled-input"
          />
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
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import Layout from '../components/Layout.vue'

const authStore = useAuthStore()
const message = ref('')
const errorMessage = ref('')

// Obtenir le mois actuel en français
const getMoisActuel = () => {
  const mois = [
    'JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
    'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'
  ]
  return mois[new Date().getMonth()]
}

const currentMonth = computed(() => getMoisActuel())
const currentYear = computed(() => new Date().getFullYear().toString())

const frais = ref({
  month: currentMonth.value,
  year: currentYear.value,
  repas: 0,
  nuitees: 0,
  etape: 0,
  km: 0,
  VIS_ID: authStore.user.VIS_ID
})

// Vérifier si une fiche existe déjà pour ce mois
const checkExistingFiche = async () => {
  try {
    const response = await fetch(`http://51.83.74.206:8000/src/getFicheFrais.php?vis_id=${authStore.user.VIS_ID}&role=VISITEUR_MEDICAL`)
    const data = await response.json()
    
    if (data.success) {
      const existingFiche = data.data.find(fiche => 
        fiche.FFR_MOIS === currentMonth.value && 
        fiche.FFR_ANNEE === currentYear.value
      )
      
      if (existingFiche) {
        errorMessage.value = "Vous avez déjà créé une fiche de frais pour ce mois."
        return true
      }
    }
    return false
  } catch (error) {
    console.error('Erreur lors de la vérification:', error)
    return true
  }
}

const submitFrais = async () => {
  if (await checkExistingFiche()) {
    return
  }

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
      // Réinitialiser le formulaire
      frais.value = {
        month: currentMonth.value,
        year: currentYear.value,
        repas: 0,
        nuitees: 0,
        etape: 0,
        km: 0,
        VIS_ID: authStore.user.VIS_ID
      }
    } else {
      message.value = data.message || 'Erreur lors de la soumission de la fiche de frais'
    }
  } catch (error) {
    message.value = 'Erreur de connexion au serveur'
  }
}

onMounted(async () => {
  await checkExistingFiche()
})
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
  .disabled-input {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}
  </style>