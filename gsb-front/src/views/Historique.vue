<template>
    <Layout>
      <div class="historique-container">
        <h1>Historique des Fiches de Frais</h1>
        <div v-if="fichesFrais.length === 0">
          <p>Aucune fiche de frais trouvée.</p>
        </div>
        <div v-else>
          <table>
            <thead>
              <tr>
                <th>ID Fiche</th>
                <th>Visiteur ID</th>
                <th>Mois</th>
                <th>Année</th>
                <th>Montant Validé</th>
                <th>Nombre de Justificatifs</th>
                <th>Date de Modification</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fiche in fichesFrais" :key="fiche.FFR_ID">
                <td>{{ fiche.FFR_ID }}</td>
                <td>{{ fiche.VIS_ID }}</td>
                <td>{{ fiche.FFR_MOIS }}</td>
                <td>{{ fiche.FFR_ANNEE }}</td>
                <td>{{ fiche.FFR_MONTANT_VALIDE }}</td>
                <td>{{ fiche.FFR_NB_JUSTIFICATIFS }}</td>
                <td>{{ fiche.FFR_DATE_MODIF }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Layout>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useAuthStore } from '../stores/auth'
  import Layout from '../components/Layout.vue'
  
  const authStore = useAuthStore()
  const fichesFrais = ref([])
  
  onMounted(async () => {
    fichesFrais.value = await authStore.fetchFichesFrais()
  })
  </script>
  
  <style scoped>
  .historique-container {
    padding: 2rem;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }
  
  h1 {
    text-align: center;
    margin-bottom: 2rem;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }
  
  thead th {
    background: #1e3c72;
    color: white;
    padding: 0.5rem;
  }
  
  tbody td {
    padding: 0.5rem;
    border: 1px solid #ddd;
  }
  </style>