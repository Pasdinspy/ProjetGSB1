<template>
  <Layout>
    <div class="historique-container">
      <!-- En-tête avec filtres et options de tri -->
      <div class="historique-header">
        <h1 class="historique-title">Historique des fiches de frais</h1>
        <div class="historique-controls">
          <div class="historique-filter-group">
            <label for="yearFilter">Année :</label>
            <select id="yearFilter" v-model="selectedYear" class="historique-filter-select" @change="applyFilters">
              <option value="">Toutes les années</option>
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="historique-filter-group">
            <label for="statusFilter">État :</label>
            <select id="statusFilter" v-model="selectedStatus" class="historique-filter-select" @change="applyFilters">
              <option value="">Tous les états</option>
              <option value="CR">Fiche créée, saisie en cours</option>
              <option value="CL">Saisie clôturée</option>
              <option value="RE">Remboursée</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Liste des fiches -->
      <div v-if="filteredAndSortedFiches.length > 0">
        <div class="historique-fiches-list">
          <div v-for="fiche in filteredAndSortedFiches" :key="fiche.FFR_ID" class="historique-fiche-card"
            :class="getFicheStatusClass(fiche.ETA_ID)">
            <div class="historique-fiche-header">
              <div class="historique-fiche-info">
                <span v-if="isAdmin" class="historique-visiteur-info">
                  {{ fiche.VIS_NOM }} {{ fiche.VIS_PRENOM }}
                </span>
                <span class="historique-period-info">
                  {{ fiche.FFR_MOIS }} {{ fiche.FFR_ANNEE }}
                </span>
                <span class="historique-date-info">
                  Modifiée le {{ formatDate(fiche.FFR_DATE_MODIF) }}
                </span>
              </div>
              <div class="historique-status-badge" :class="getStatusClass(fiche.ETA_ID)">
                {{ getStatusText(fiche.ETA_ID) }}
              </div>
            </div>

            <div class="historique-fiche-body">
              <div class="historique-montant-section">
                <span class="historique-montant-label">Montant total</span>
                <span class="historique-montant-value">
                  {{ formatMontant(fiche.FFR_MONTANT_VALIDE) }} €
                </span>
              </div>
              <div class="historique-justificatifs-section">
                <span class="historique-justificatifs-label">Justificatifs</span>
                <span class="historique-justificatifs-value">
                  {{ fiche.FFR_NB_JUSTIFICATIFS }}
                </span>
              </div>
            </div>

            <div class="historique-fiche-actions">
              <button @click="viewDetails(fiche)" class="historique-btn historique-btn-view">
                <i class="fas fa-eye"></i> Détails
              </button>
              <button v-if="canEditFiche(fiche)" @click="editFiche(fiche)" class="historique-btn historique-btn-edit">
                <i class="fas fa-edit"></i> Modifier
              </button>
              <button v-if="canDeleteFiche(fiche)" @click="confirmDelete(fiche)"
                class="historique-btn historique-btn-delete">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="historique-empty-state">
        <i class="fas fa-folder-open"></i>
        <p>Aucune fiche de frais trouvée</p>
      </div>

      <!-- Modal de détails -->
      <Modal v-if="selectedFiche" @close="selectedFiche = null">
        <template #header>
          <h3 class="historique-modal-title">Détails de la fiche de frais</h3>
        </template>
        <template #body>
          <div class="historique-details-content">
            <div class="historique-details-section">
              <h4>Informations générales</h4>
              <div class="historique-detail-row">
                <span class="historique-detail-label">Période :</span>
                <span class="historique-detail-value">
                  {{ selectedFiche.FFR_MOIS }} {{ selectedFiche.FFR_ANNEE }}
                </span>
              </div>
              <div class="historique-detail-row">
                <span class="historique-detail-label">État :</span>
                <span class="historique-detail-value">
                  <span class="historique-status-badge" :class="getStatusClass(selectedFiche.ETA_ID)">
                    {{ getStatusText(selectedFiche.ETA_ID) }}
                  </span>
                </span>
              </div>
              <div class="historique-detail-row">
                <span class="historique-detail-label">Date de modification :</span>
                <span class="historique-detail-value">
                  {{ formatDate(selectedFiche.FFR_DATE_MODIF) }}
                </span>
              </div>
            </div>

            <div class="historique-details-section">
              <h4>Frais forfaitaires</h4>
              <div class="historique-frais-grid">
                <div class="historique-frais-item">
                  <i class="fas fa-utensils"></i>
                  <span class="historique-frais-label">Repas</span>
                  <span class="historique-frais-value">{{ selectedFiche.repas || 0 }}</span>
                </div>
                <div class="historique-frais-item">
                  <i class="fas fa-bed"></i>
                  <span class="historique-frais-label">Nuitées</span>
                  <span class="historique-frais-value">{{ selectedFiche.nuitees || 0 }}</span>
                </div>
                <div class="historique-frais-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <span class="historique-frais-label">Étapes</span>
                  <span class="historique-frais-value">{{ selectedFiche.etape || 0 }}</span>
                </div>
                <div class="historique-frais-item">
                  <i class="fas fa-road"></i>
                  <span class="historique-frais-label">Kilométrage</span>
                  <span class="historique-frais-value">{{ selectedFiche.km || 0 }} km</span>
                </div>
              </div>
            </div>

            <div class="historique-details-section">
              <h4>Montant total</h4>
              <div class="historique-montant-total">
                {{ formatMontant(selectedFiche.FFR_MONTANT_VALIDE) }} €
              </div>
            </div>
          </div>
        </template>
      </Modal>

      <!-- Modal d'édition -->
      <Modal v-if="editingFiche" @close="editingFiche = null">
        <template #header>
          <h3 class="historique-modal-title">Modifier la fiche de frais</h3>
        </template>
        <template #body>
          <form @submit.prevent="updateFiche" class="historique-edit-form">
            <div class="historique-form-grid">
              <div class="historique-form-group">
                <label for="repas">
                  <i class="fas fa-utensils"></i> Repas
                </label>
                <input type="number" id="repas" v-model="editForm.repas" min="0" required
                  class="historique-form-input" />
              </div>
              <div class="historique-form-group">
                <label for="nuitees">
                  <i class="fas fa-bed"></i> Nuitées
                </label>
                <input type="number" id="nuitees" v-model="editForm.nuitees" min="0" required
                  class="historique-form-input" />
              </div>
              <div class="historique-form-group">
                <label for="etape">
                  <i class="fas fa-map-marker-alt"></i> Étapes
                </label>
                <input type="number" id="etape" v-model="editForm.etape" min="0" required
                  class="historique-form-input" />
              </div>
              <div class="historique-form-group">
                <label for="km">
                  <i class="fas fa-road"></i> Kilométrage
                </label>
                <input type="number" id="km" v-model="editForm.km" min="0" required class="historique-form-input" />
              </div>
            </div>
            <div class="historique-form-actions">
              <button type="submit" class="historique-btn historique-btn-success">
                <i class="fas fa-save"></i> Enregistrer
              </button>
              <button type="button" @click="editingFiche = null" class="historique-btn historique-btn-secondary">
                <i class="fas fa-times"></i> Annuler
              </button>
            </div>
          </form>
        </template>
      </Modal>

      <!-- Modal de confirmation de suppression -->
      <Modal v-if="ficheToDelete" @close="ficheToDelete = null">
        <template #header>
          <h3 class="historique-modal-title">Confirmation de suppression</h3>
        </template>
        <template #body>
          <div class="historique-delete-confirmation">
            <i class="fas fa-exclamation-triangle historique-warning-icon"></i>
            <p>Êtes-vous sûr de vouloir supprimer cette fiche de frais ?</p>
            <p class="historique-delete-warning">Cette action est irréversible.</p>
            <div class="historique-confirmation-actions">
              <button @click="deleteFiche" class="historique-btn historique-btn-danger">
                <i class="fas fa-trash"></i> Supprimer
              </button>
              <button @click="ficheToDelete = null" class="historique-btn historique-btn-secondary">
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
const ficheToDelete = ref(null)
const editingFiche = ref(null)
const selectedYear = ref('')
const selectedStatus = ref('')
const sortOrder = ref('desc')
const editForm = ref({
  repas: 0,
  nuitees: 0,
  etape: 0,
  km: 0
})

const isAdmin = computed(() => authStore.isAdministrateur())

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

  // Tri par date
  result.sort((a, b) => {
    const dateA = new Date(a.FFR_DATE_MODIF)
    const dateB = new Date(b.FFR_DATE_MODIF)
    return sortOrder.value === 'desc' ? dateB - dateA : dateA - dateB
  })

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
    'CR': 'historique-status-pending',
    'CL': 'historique-status-closed',
    'RE': 'historique-status-reimbursed'
  }
  return statusClasses[status] || 'historique-status-default'
}

const getStatusText = (status) => {
  const statusTexts = {
    'CR': 'Fiche créée, saisie en cours',
    'CL': 'Saisie clôturée',
    'RE': 'Remboursée'
  }
  return statusTexts[status] || status
}

const getFicheStatusClass = (status) => {
  return {
    'historique-fiche-pending': status === 'CR',
    'historique-fiche-closed': status === 'CL',
    'historique-fiche-reimbursed': status === 'RE'
  }
}
const applyFilters = () => {
  // Cette fonction est appelée quand les filtres changent
  // Le computed property filteredAndSortedFiches se met à jour automatiquement
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
    const baseUrl = 'http://51.83.74.206:8000/src/getFicheFrais.php'
    const params = new URLSearchParams({
      role: authStore.user.role,
      ...(authStore.user.VIS_ID && { vis_id: authStore.user.VIS_ID })
    })

    const response = await fetch(`${baseUrl}?${params}`)
    const result = await response.json()

    if (result.success) {
      fiches.value = result.data
    }
  } catch (error) {
    console.error('Erreur lors du chargement des fiches:', error)
  }
}

const canEditFiche = (fiche) => {
  return fiche.ETA_ID === 'CR' &&
    (isAdmin.value || authStore.user.VIS_ID === fiche.VIS_ID)
}

const canDeleteFiche = (fiche) => {
  return fiche.ETA_ID === 'CR' &&
    (isAdmin.value || authStore.user.VIS_ID === fiche.VIS_ID)
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
    console.error('Erreur lors du chargement des frais forfaits:', error)
  }
}

const editFiche = async (fiche) => {
  try {
    const response = await fetch(`http://51.83.74.206:8000/src/getFraisForfait.php?FFR_ID=${fiche.FFR_ID}`)
    const result = await response.json()

    if (result.success) {
      editingFiche.value = fiche
      editForm.value = {
        repas: result.data.repas,
        nuitees: result.data.nuitees,
        etape: result.data.etape,
        km: result.data.km
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des frais forfaits:', error)
  }
}

const updateFiche = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/updateFicheFrais.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        FFR_ID: editingFiche.value.FFR_ID,
        ...editForm.value
      })
    })

    const result = await response.json()
    if (result.success) {
      await loadFiches()
      editingFiche.value = null
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
  }
}

const confirmDelete = (fiche) => {
  ficheToDelete.value = fiche
}

const deleteFiche = async () => {
  try {
    const response = await fetch('http://51.83.74.206:8000/src/deleteFicheFrais.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        FFR_ID: ficheToDelete.value.FFR_ID,
        VIS_ID: authStore.user.VIS_ID,
        role: authStore.user.role
      })
    })

    const result = await response.json()
    if (result.success) {
      await loadFiches()
      ficheToDelete.value = null
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

onMounted(async () => {
  await Promise.all([loadFiches(), loadYears()])
})
</script>

<style scoped>

</style>