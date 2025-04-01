<template>
  <Layout>
    <div class="payments-container">
      <!-- En-tête avec filtres -->
      <div class="header">
        <h1 class="title">Validation des paiements</h1>
        <div class="filters">
          <select v-model="selectedStatus" class="filter-select" @change="filterFiches">
            <option value="">Tous les états</option>
            <option value="CL">Clôturées</option>
            <option value="VA">Validées</option>
            <option value="RE">Remboursées</option>
          </select>
          <select v-model="selectedYear" class="filter-select" @change="filterFiches">
            <option value="">Toutes les années</option>
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
      </div>

      <!-- Tableau des fiches -->
      <div class="fiches-table">
        <table v-if="filteredFiches.length > 0">
          <thead>
            <tr>
              <th>Visiteur</th>
              <th>Période</th>
              <th>Montant</th>
              <th>Justificatifs</th>
              <th>État</th>
              <th>Date modification</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="fiche in filteredFiches" 
                :key="fiche.FFR_ID"
                :class="getStatusClass(fiche.ETA_ID)">
              <td>{{ fiche.VIS_NOM }} {{ fiche.VIS_PRENOM }}</td>
              <td>{{ fiche.FFR_MOIS }} {{ fiche.FFR_ANNEE }}</td>
              <td>{{ formatMontant(fiche.FFR_MONTANT_VALIDE) }} €</td>
              <td>{{ fiche.FFR_NB_JUSTIFICATIFS }}</td>
              <td>
                <span class="status-badge" :class="getStatusClass(fiche.ETA_ID)">
                  {{ getStatusText(fiche.ETA_ID) }}
                </span>
              </td>
              <td>{{ formatDate(fiche.FFR_DATE_MODIF) }}</td>
              <td class="actions">
                <button v-if="fiche.ETA_ID === 'CL'"
                        @click="validateFiche(fiche)"
                        class="btn btn-validate">
                  <i class="fas fa-check"></i> Valider
                </button>
                <button v-if="fiche.ETA_ID === 'VA'"
                        @click="markAsReimbursed(fiche)"
                        class="btn btn-reimburse">
                  <i class="fas fa-euro-sign"></i> Marquer comme remboursée
                </button>
                <button @click="viewDetails(fiche)"
                        class="btn btn-view">
                  <i class="fas fa-eye"></i> Détails
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <i class="fas fa-file-invoice-dollar"></i>
          <p>Aucune fiche de frais à traiter</p>
        </div>
      </div>

      <!-- Modal de détails -->
      <modal v-if="selectedFiche" @close="selectedFiche = null">
        <template #header>
          <h3>Détails de la fiche de frais</h3>
        </template>
        <template #body>
          <div class="detail-content">
            <div class="detail-section">
              <h4>Informations du visiteur</h4>
              <p><strong>Nom :</strong> {{ selectedFiche.VIS_NOM }} {{ selectedFiche.VIS_PRENOM }}</p>
              <p><strong>Période :</strong> {{ selectedFiche.FFR_MOIS }} {{ selectedFiche.FFR_ANNEE }}</p>
            </div>

            <div class="detail-section">
              <h4>Frais forfaitaires</h4>
              <div class="frais-grid">
                <div v-for="frais in selectedFiche.fraisForfait" 
                     :key="frais.FOR_ID" 
                     class="frais-item">
                  <span class="frais-label">{{ frais.FOR_LIB }}</span>
                  <span class="frais-value">
                    {{ frais.quantite }} x {{ frais.FOR_MONTANT }}€ = 
                    {{ formatMontant(frais.quantite * frais.FOR_MONTANT) }}€
                  </span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h4>Total des frais</h4>
              <p class="total-amount">{{ formatMontant(selectedFiche.FFR_MONTANT_VALIDE) }} €</p>
            </div>
          </div>
        </template>
        <template #footer>
          <div class="modal-actions">
            <button @click="selectedFiche = null" class="btn btn-secondary">
              Fermer
            </button>
          </div>
        </template>
      </modal>
    </div>
  </Layout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import Layout from '../components/Layout.vue'
import Modal from '../components/Modal.vue'

const authStore = useAuthStore()
const fiches = ref([])
const selectedFiche = ref(null)
const selectedStatus = ref('')
const selectedYear = ref('')
const years = ref([])

// Formater les montants
const formatMontant = (montant) => {
  return Number(montant).toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// Formater les dates
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR')
}

// Obtenir la classe CSS selon l'état
const getStatusClass = (status) => {
  const statusClasses = {
    'CL': 'status-closed',
    'VA': 'status-validated',
    'RE': 'status-reimbursed'
  }
  return statusClasses[status] || ''
}

// Obtenir le texte de l'état
const getStatusText = (status) => {
  const statusTexts = {
    'CL': 'Clôturée',
    'VA': 'Validée',
    'RE': 'Remboursée'
  }
  return statusTexts[status] || status
}

// Filtrer les fiches
const filteredFiches = computed(() => {
  return fiches.value.filter(fiche => {
    if (selectedStatus.value && fiche.ETA_ID !== selectedStatus.value) return false
    if (selectedYear.value && fiche.FFR_ANNEE !== selectedYear.value) return false
    return true
  })
})

// Charger les fiches
const loadFiches = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/getFicheFrais.php')
    const result = await response.json()
    if (result.success) {
      fiches.value = result.data
      // Extraire les années uniques
      years.value = [...new Set(result.data.map(fiche => fiche.FFR_ANNEE))]
    }
  } catch (error) {
    console.error('Erreur lors du chargement des fiches:', error)
  }
}

// Valider une fiche
const validateFiche = async (fiche) => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/updateFicheState.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        FFR_ID: fiche.FFR_ID,
        ETA_ID: 'VA'
      })
    })
    const result = await response.json()
    if (result.success) {
      await loadFiches()
    }
  } catch (error) {
    console.error('Erreur lors de la validation:', error)
  }
}

// Marquer comme remboursée
const markAsReimbursed = async (fiche) => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/updateFicheState.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        FFR_ID: fiche.FFR_ID,
        ETA_ID: 'RE'
      })
    })
    const result = await response.json()
    if (result.success) {
      await loadFiches()
    }
  } catch (error) {
    console.error('Erreur lors du remboursement:', error)
  }
}

// Voir les détails
const viewDetails = async (fiche) => {
  try {
    const response = await fetch(`http://51.83.74.206:8000/src/getFraisForfait.php?FFR_ID=${fiche.FFR_ID}`)
    const result = await response.json()
    if (result.success) {
      selectedFiche.value = {
        ...fiche,
        fraisForfait: result.data
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des détails:', error)
  }
}

onMounted(loadFiches)
</script>

<style scoped>
.payments-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.title {
  font-size: 1.75rem;
  color: #2c3e50;
}

.filters {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  min-width: 200px;
}

.fiches-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  overflow: hidden;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: 600;
  color: #2c3e50;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-closed {
  background: #e9ecef;
  color: #495057;
}

.status-validated {
  background: #d4edda;
  color: #155724;
}

.status-reimbursed {
  background: #cce5ff;
  color: #004085;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-validate {
  background: #28a745;
  color: white;
}

.btn-reimburse {
  background: #17a2b8;
  color: white;
}

.btn-view {
  background: #6c757d;
  color: white;
}

.empty-state {
  text-align: center;
  padding: 3rem;
}

.empty-state i {
  font-size: 3rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.detail-content {
  padding: 1rem;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h4 {
  color: #2c3e50;
  margin-bottom: 1rem;
}

.frais-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.frais-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 4px;
}

.total-amount {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2c3e50;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 1rem;
  }

  .filters {
    flex-direction: column;
    width: 100%;
  }

  .filter-select {
    width: 100%;
  }

  .actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>