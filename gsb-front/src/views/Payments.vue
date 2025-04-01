<template>
  <Layout>
    <div class="payments-container">
      <!-- En-tête avec filtres -->
      <div class="payments-header">
        <h1 class="payments-title">Gestion des Remboursements</h1>
        <div class="payments-controls">
          <div class="payments-filter-group">
            <label for="yearFilter">Année :</label>
            <select id="yearFilter" v-model="selectedYear" class="payments-filter-select" @change="applyFilters">
              <option value="">Toutes les années</option>
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="payments-filter-group">
            <label for="statusFilter">État :</label>
            <select id="statusFilter" v-model="selectedStatus" class="payments-filter-select" @change="applyFilters">
              <option value="">Tous les états</option>
              <option value="CL">Saisie clôturée</option>
              <option value="RE">Remboursée</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Liste des fiches -->
      <div v-if="filteredAndSortedFiches.length > 0">
        <div class="payments-fiches-list">
          <div v-for="fiche in filteredAndSortedFiches" :key="fiche.FFR_ID" 
               class="payments-fiche-card"
               :class="getFicheStatusClass(fiche.ETA_ID)">
            <div class="payments-fiche-header">
              <div class="payments-fiche-info">
                <span class="payments-visiteur-info">
                  {{ fiche.VIS_NOM }} {{ fiche.VIS_PRENOM }}
                </span>
                <span class="payments-period-info">
                  {{ fiche.FFR_MOIS }} {{ fiche.FFR_ANNEE }}
                </span>
                <span class="payments-date-info">
                  Modifiée le {{ formatDate(fiche.FFR_DATE_MODIF) }}
                </span>
              </div>
              <div class="payments-status-badge" :class="getStatusClass(fiche.ETA_ID)">
                {{ getStatusText(fiche.ETA_ID) }}
              </div>
            </div>

            <div class="payments-fiche-body">
              <div class="payments-montant-section">
                <span class="payments-montant-label">Montant total</span>
                <span class="payments-montant-value">
                  {{ formatMontant(fiche.FFR_MONTANT_VALIDE) }} €
                </span>
              </div>
              <div class="payments-justificatifs-section">
                <span class="payments-justificatifs-label">Justificatifs</span>
                <span class="payments-justificatifs-value">
                  {{ fiche.FFR_NB_JUSTIFICATIFS }}
                </span>
              </div>
            </div>

            <div class="payments-fiche-actions">
              <button @click="viewDetails(fiche)" class="payments-btn payments-btn-view">
                <i class="fas fa-eye"></i> Détails
              </button>
              <button v-if="canMarkAsReimbursed(fiche)" 
                      @click="confirmReimbursement(fiche)" 
                      class="payments-btn payments-btn-success">
                <i class="fas fa-check"></i> Marquer comme remboursée
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="payments-empty-state">
        <i class="fas fa-folder-open"></i>
        <p>Aucune fiche de frais à traiter</p>
      </div>

      <!-- Modal de détails -->
      <Modal v-if="selectedFiche" @close="selectedFiche = null">
        <template #header>
          <h3 class="payments-modal-title">Détails de la fiche de frais</h3>
        </template>
        <template #body>
          <div class="payments-details-content">
            <div class="payments-details-section">
              <h4>Informations du visiteur</h4>
              <div class="payments-detail-row">
                <span class="payments-detail-label">Nom :</span>
                <span class="payments-detail-value">
                  {{ selectedFiche.VIS_NOM }} {{ selectedFiche.VIS_PRENOM }}
                </span>
              </div>
              <div class="payments-detail-row">
                <span class="payments-detail-label">Période :</span>
                <span class="payments-detail-value">
                  {{ selectedFiche.FFR_MOIS }} {{ selectedFiche.FFR_ANNEE }}
                </span>
              </div>
              <div class="payments-detail-row">
                <span class="payments-detail-label">État :</span>
                <span class="payments-detail-value">
                  <span class="payments-status-badge" :class="getStatusClass(selectedFiche.ETA_ID)">
                    {{ getStatusText(selectedFiche.ETA_ID) }}
                  </span>
                </span>
              </div>
            </div>

            <div class="payments-details-section">
              <h4>Frais forfaitaires</h4>
              <div class="payments-frais-grid">
                <div class="payments-frais-item">
                  <i class="fas fa-utensils"></i>
                  <span class="payments-frais-label">Repas</span>
                  <span class="payments-frais-value">{{ selectedFiche.repas || 0 }}</span>
                </div>
                <div class="payments-frais-item">
                  <i class="fas fa-bed"></i>
                  <span class="payments-frais-label">Nuitées</span>
                  <span class="payments-frais-value">{{ selectedFiche.nuitees || 0 }}</span>
                </div>
                <div class="payments-frais-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <span class="payments-frais-label">Étapes</span>
                  <span class="payments-frais-value">{{ selectedFiche.etape || 0 }}</span>
                </div>
                <div class="payments-frais-item">
                  <i class="fas fa-road"></i>
                  <span class="payments-frais-label">Kilométrage</span>
                  <span class="payments-frais-value">{{ selectedFiche.km || 0 }} km</span>
                </div>
              </div>
            </div>

            <div class="payments-details-section">
              <h4>Montant total</h4>
              <div class="payments-montant-total">
                {{ formatMontant(selectedFiche.FFR_MONTANT_VALIDE) }} €
              </div>
            </div>

            <div v-if="canMarkAsReimbursed(selectedFiche)" class="payments-modal-actions">
              <button @click="confirmReimbursement(selectedFiche)" 
                      class="payments-btn payments-btn-success payments-btn-large">
                <i class="fas fa-check"></i> Valider le remboursement
              </button>
            </div>
          </div>
        </template>
      </Modal>

      <!-- Modal de confirmation -->
      <Modal v-if="ficheToReimburse" @close="ficheToReimburse = null">
        <template #header>
          <h3 class="payments-modal-title">Confirmation de remboursement</h3>
        </template>
        <template #body>
          <div class="payments-confirmation-content">
            <i class="fas fa-check-circle payments-confirmation-icon"></i>
            <p>Êtes-vous sûr de vouloir marquer cette fiche comme remboursée ?</p>
            <p class="payments-confirmation-details">
              Visiteur : {{ ficheToReimburse?.VIS_NOM }} {{ ficheToReimburse?.VIS_PRENOM }}<br>
              Montant : {{ formatMontant(ficheToReimburse?.FFR_MONTANT_VALIDE) }} €
            </p>
            <div class="payments-confirmation-actions">
              <button @click="markAsReimbursed" class="payments-btn payments-btn-success">
                <i class="fas fa-check"></i> Confirmer
              </button>
              <button @click="ficheToReimburse = null" class="payments-btn payments-btn-secondary">
                <i class="fas fa-times"></i> Annuler
              </button>
            </div>
          </div>
        </template>
      </Modal>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import Layout from '../components/Layout.vue'
import Modal from '../components/Modal.vue'

const authStore = useAuthStore()
const fiches = ref([])
const years = ref([])
const selectedFiche = ref(null)
const ficheToReimburse = ref(null)
const selectedYear = ref('')
const selectedStatus = ref('')

// Computed property pour filtrer et trier les fiches
const filteredAndSortedFiches = computed(() => {
  let result = [...fiches.value]

  // Filtre par année
  if (selectedYear.value) {
    result = result.filter(fiche => fiche.FFR_ANNEE === selectedYear.value)
  }

  // Filtre par état
  if (selectedStatus.value) {
    result = result.filter(fiche => fiche.ETA_ID === selectedStatus.value)
  }

  // Tri par date de modification (plus récent en premier)
  result.sort((a, b) => new Date(b.FFR_DATE_MODIF) - new Date(a.FFR_DATE_MODIF))

  return result
})

const formatMontant = (montant) => {
  return Number(montant).toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR')
}

const getStatusClass = (status) => {
  const statusClasses = {
    'CL': 'payments-status-pending',
    'RE': 'payments-status-reimbursed'
  }
  return statusClasses[status] || 'payments-status-default'
}

const getStatusText = (status) => {
  const statusTexts = {
    'CL': 'Saisie clôturée',
    'RE': 'Remboursée'
  }
  return statusTexts[status] || status
}

const getFicheStatusClass = (status) => {
  return {
    'payments-fiche-pending': status === 'CL',
    'payments-fiche-reimbursed': status === 'RE'
  }
}

const canMarkAsReimbursed = (fiche) => {
  return fiche.ETA_ID === 'CL'
}

const applyFilters = () => {
  // Les filtres sont appliqués automatiquement via le computed property
}

const loadYears = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/getYears.php')
    const result = await response.json()
    if (result.success) {
      years.value = result.data
    }
  } catch (error) {
    console.error('Erreur lors du chargement des années:', error)
  }
}

const loadFiches = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/getFicheFrais.php')
    const result = await response.json()
    if (result.success) {
      fiches.value = result.data.filter(fiche => fiche.ETA_ID === 'CL'|| fiche.ETA_ID === 'RE') 
    }
  } catch (error) {
    console.error('Erreur lors du chargement des fiches:', error)
  }
}

const viewDetails = async (fiche) => {
  try {
    const response = await fetch(`http://51.83.74.206:8000/src/getFraisForfait.php?FFR_ID=${fiche.FFR_ID}`)
    const result = await response.json()

    if (result.success) {
      selectedFiche.value = {
        ...fiche,
        repas: result.data.repas,
        nuitees: result.data.nuitees,
        etape: result.data.etape,
        km: result.data.km
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des détails:', error)
  }
}

const confirmReimbursement = (fiche) => {
  ficheToReimburse.value = fiche
}

const markAsReimbursed = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/updateFicheState.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        FFR_ID: ficheToReimburse.value.FFR_ID,
        ETA_ID: 'RE'
      })
    })

    const result = await response.json()
    if (result.success) {
      await loadFiches()
      ficheToReimburse.value = null
      if (selectedFiche.value?.FFR_ID === ficheToReimburse.value.FFR_ID) {
        selectedFiche.value = null
      }
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du statut:', error)
  }
}

onMounted(async () => {
  await Promise.all([loadFiches(), loadYears()])
})
</script>

<style scoped>
/* Les styles sont similaires à ceux de Historique.vue, 
   mais avec le préfixe 'payments-' au lieu de 'historique-' */


/* ... Autres styles similaires à Historique.vue ... */
</style>