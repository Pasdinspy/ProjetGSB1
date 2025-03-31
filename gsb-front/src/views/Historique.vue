<template>
  <Layout>
    <div class="historique-container">
      <!-- En-tête avec filtres -->
      <div class="header">
        <h1 class="title">Historique des fiches de frais</h1>
        <div class="filters">
          <select v-model="selectedYear" class="filter-select" @change="loadFiches">
            <option value="">Toutes les années</option>
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
      </div>

      <!-- Tableau des fiches -->
      <div v-if="groupedFiches && Object.keys(groupedFiches).length > 0">
        <div v-for="(yearData, year) in groupedFiches" :key="year" class="year-section">
          <h2 class="year-title">{{ year }}</h2>
          
          <div v-for="(monthData, month) in yearData" :key="month" class="month-section">
            <div class="month-header">
              <h3>{{ month }}</h3>
            </div>

            <div class="fiche-card" 
                 v-for="fiche in monthData" 
                 :key="fiche.FFR_ID"
                 :class="getFicheStatusClass(fiche.ETA_ID)">
              <div class="fiche-header">
                <div class="fiche-info">
                  <span v-if="isAdmin" class="visiteur-info">
                    {{ fiche.VIS_NOM }} {{ fiche.VIS_PRENOM }}
                  </span>
                  <span class="date-info">
                    Modifiée le {{ formatDate(fiche.FFR_DATE_MODIF) }}
                  </span>
                </div>
                <div class="status-badge" :class="getStatusClass(fiche.ETA_ID)">
                  {{ getStatusText(fiche.ETA_ID) }}
                </div>
              </div>

              <div class="fiche-body">
                <div class="montant-section">
                  <span class="montant-label">Montant total</span>
                  <span class="montant-value">{{ formatMontant(fiche.FFR_MONTANT_VALIDE) }} €</span>
                </div>
                <div class="justificatifs-section">
                  <span class="justificatifs-label">Justificatifs</span>
                  <span class="justificatifs-value">{{ fiche.FFR_NB_JUSTIFICATIFS }}</span>
                </div>
              </div>

              <div class="fiche-actions">
                <button @click="viewDetails(fiche)" class="btn btn-view">
                  <i class="fas fa-eye"></i> Détails
                </button>
                <button v-if="canEditFiche(fiche)" 
                        @click="editFiche(fiche)" 
                        class="btn btn-edit">
                  <i class="fas fa-edit"></i> Modifier
                </button>
                <button v-if="canDeleteFiche(fiche)" 
                        @click="confirmDelete(fiche)" 
                        class="btn btn-delete">
                  <i class="fas fa-trash"></i> Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-else class="empty-state">
        <i class="fas fa-folder-open"></i>
        <p>Aucune fiche de frais trouvée</p>
      </div>

      <!-- Modal de détails -->
      <modal v-if="selectedFiche" @close="selectedFiche = null">
        <template #header>
          <h3>Détails de la fiche de frais</h3>
        </template>
        <template #body>
          <div class="details-content">
            <div class="details-section">
              <h4>Informations générales</h4>
              <div class="detail-row">
                <span class="detail-label">Période :</span>
                <span class="detail-value">{{ selectedFiche.FFR_MOIS }} {{ selectedFiche.FFR_ANNEE }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">État :</span>
                <span class="detail-value">
                  <span class="status-badge" :class="getStatusClass(selectedFiche.ETA_ID)">
                    {{ getStatusText(selectedFiche.ETA_ID) }}
                  </span>
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date de modification :</span>
                <span class="detail-value">{{ formatDate(selectedFiche.FFR_DATE_MODIF) }}</span>
              </div>
            </div>

            <div class="details-section">
              <h4>Frais forfaitaires</h4>
              <div class="frais-grid">
                <div class="frais-item">
                  <i class="fas fa-utensils"></i>
                  <span class="frais-label">Repas</span>
                  <span class="frais-value">{{ selectedFiche.repas || 0 }}</span>
                </div>
                <div class="frais-item">
                  <i class="fas fa-bed"></i>
                  <span class="frais-label">Nuitées</span>
                  <span class="frais-value">{{ selectedFiche.nuitees || 0 }}</span>
                </div>
                <div class="frais-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <span class="frais-label">Étapes</span>
                  <span class="frais-value">{{ selectedFiche.etape || 0 }}</span>
                </div>
                <div class="frais-item">
                  <i class="fas fa-road"></i>
                  <span class="frais-label">Kilométrage</span>
                  <span class="frais-value">{{ selectedFiche.km || 0 }} km</span>
                </div>
              </div>
            </div>

            <div class="details-section">
              <h4>Montant total</h4>
              <div class="montant-total">
                {{ formatMontant(selectedFiche.FFR_MONTANT_VALIDE) }} €
              </div>
            </div>
          </div>
        </template>
      </modal>

      <!-- Modal d'édition -->
      <modal v-if="editingFiche" @close="editingFiche = null">
        <template #header>
          <h3>Modifier la fiche de frais</h3>
        </template>
        <template #body>
          <form @submit.prevent="updateFiche" class="edit-form">
            <div class="form-grid">
              <div class="form-group">
                <label for="repas">
                  <i class="fas fa-utensils"></i> Repas
                </label>
                <input 
                  type="number" 
                  id="repas" 
                  v-model="editForm.repas" 
                  min="0" 
                  required
                />
              </div>
              <div class="form-group">
                <label for="nuitees">
                  <i class="fas fa-bed"></i> Nuitées
                </label>
                <input 
                  type="number" 
                  id="nuitees" 
                  v-model="editForm.nuitees" 
                  min="0" 
                  required
                />
              </div>
              <div class="form-group">
                <label for="etape">
                  <i class="fas fa-map-marker-alt"></i> Étapes
                </label>
                <input 
                  type="number" 
                  id="etape" 
                  v-model="editForm.etape" 
                  min="0" 
                  required
                />
              </div>
              <div class="form-group">
                <label for="km">
                  <i class="fas fa-road"></i> Kilométrage
                </label>
                <input 
                  type="number" 
                  id="km" 
                  v-model="editForm.km" 
                  min="0" 
                  required
                />
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Enregistrer
              </button>
              <button type="button" @click="editingFiche = null" class="btn btn-secondary">
                <i class="fas fa-times"></i> Annuler
              </button>
            </div>
          </form>
        </template>
      </modal>

      <!-- Modal de confirmation de suppression -->
      <modal v-if="ficheToDelete" @close="ficheToDelete = null">
        <template #header>
          <h3>Confirmation de suppression</h3>
        </template>
        <template #body>
          <div class="delete-confirmation">
            <i class="fas fa-exclamation-triangle warning-icon"></i>
            <p>Êtes-vous sûr de vouloir supprimer cette fiche de frais ?</p>
            <p class="delete-warning">Cette action est irréversible.</p>
            <div class="confirmation-actions">
              <button @click="deleteFiche" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
              </button>
              <button @click="ficheToDelete = null" class="btn btn-secondary">
                <i class="fas fa-times"></i> Annuler
              </button>
            </div>
          </div>
        </template>
      </modal>
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
const editForm = ref({
  repas: 0,
  nuitees: 0,
  etape: 0,
  km: 0
})

const isAdmin = computed(() => authStore.isAdministrateur())

// Grouper les fiches par année et mois
const groupedFiches = computed(() => {
  if (!fiches.value.length) return null

  let grouped = fiches.value.reduce((acc, fiche) => {
    if (selectedYear.value && fiche.FFR_ANNEE !== selectedYear.value) {
      return acc
    }

    if (!acc[fiche.FFR_ANNEE]) {
      acc[fiche.FFR_ANNEE] = {}
    }
    if (!acc[fiche.FFR_ANNEE][fiche.FFR_MOIS]) {
      acc[fiche.FFR_ANNEE][fiche.FFR_MOIS] = []
    }
    acc[fiche.FFR_ANNEE][fiche.FFR_MOIS].push(fiche)
    return acc
  }, {})

  // Trier les années et mois
  return Object.keys(grouped)
    .sort((a, b) => b - a)
    .reduce((acc, year) => {
      acc[year] = Object.keys(grouped[year])
        .sort((a, b) => {
          const months = ['JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
                         'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE']
          return months.indexOf(b) - months.indexOf(a)
        })
        .reduce((monthAcc, month) => {
          monthAcc[month] = grouped[year][month]
          return monthAcc
        }, {})
      return acc
    }, {})
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
    'CR': 'status-pending',
    'CL': 'status-closed',
    'VA': 'status-validated',
    'RE': 'status-reimbursed'
  }
  return statusClasses[status] || 'status-default'
}

const getStatusText = (status) => {
  const statusTexts = {
    'CR': 'En cours',
    'CL': 'Clôturée',
    'VA': 'Validée',
    'RE': 'Remboursée'
  }
  return statusTexts[status] || status
}

const getFicheStatusClass = (status) => {
  return {
    'fiche-pending': status === 'CR',
    'fiche-closed': status === 'CL',
    'fiche-validated': status === 'VA',
    'fiche-reimbursed': status === 'RE'
  }
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
.historique-container {
  padding: 2rem;
  max-width: 1200px;
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
  font-weight: 600;
}

.year-section {
  margin-bottom: 2rem;
}

.year-title {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e9ecef;
}

.month-section {
  margin-bottom: 1.5rem;
}

.month-header {
  margin-bottom: 1rem;
  color: #6c757d;
}

.fiche-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 1rem;
  padding: 1.5rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.fiche-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.fiche-pending { border-left: 4px solid #ffd700; }
.fiche-closed { border-left: 4px solid #6c757d; }
.fiche-validated { border-left: 4px solid #28a745; }
.fiche-reimbursed { border-left: 4px solid #17a2b8; }

.fiche-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.fiche-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.visiteur-info {
  font-weight: 600;
  color: #2c3e50;
}

.date-info {
  color: #6c757d;
  font-size: 0.875rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
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

.fiche-body {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 1rem 0;
  padding: 1rem 0;
  border-top: 1px solid #e9ecef;
  border-bottom: 1px solid #e9ecef;
}

.montant-section,
.justificatifs-section {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.montant-label,
.justificatifs-label {
  color: #6c757d;
  font-size: 0.875rem;
}

.montant-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
}

.fiche-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
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
  transition: all 0.2s ease;
}

.btn i {
  font-size: 0.875rem;
}

.btn-view {
  background: #e9ecef;
  color: #495057;
}

.btn-edit {
  background: #fff3cd;
  color: #856404;
}

.btn-delete {
  background: #f8d7da;
  color: #721c24;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-state i {
  font-size: 3rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.details-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.details-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.details-section h4 {
  color: #2c3e50;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.frais-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.frais-item {
  background: white;
  padding: 1rem;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.frais-item i {
  font-size: 1.5rem;
  color: #6c757d;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #495057;
  font-weight: 500;
}

.form-group input {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
}

.delete-confirmation {
  text-align: center;
  padding: 1rem;
}

.warning-icon {
  font-size: 3rem;
  color: #dc3545;
  margin-bottom: 1rem;
}

.delete-warning {
  color: #721c24;
  margin-top: 0.5rem;
}

.confirmation-actions {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
}

@media (max-width: 768px) {
  .historique-container {
    padding: 1rem;
  }

  .header {
    flex-direction: column;
    gap: 1rem;
  }

  .fiche-body {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .fiche-actions {
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
/* Nouveaux styles */
.filters {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.filter-select {
  min-width: 200px;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  background-color: white;
}

.btn-refresh {
  background-color: #4a5568;
  color: white;
  padding: 0.5rem 1rem;
}

.btn-refresh:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-card i {
  font-size: 1.5rem;
  color: #4a5568;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
}

.stat-label {
  font-size: 0.875rem;
  color: #718096;
}

.periode-info {
  font-size: 0.875rem;
  color: #4a5568;
  font-weight: 500;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-page {
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  background-color: white;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.875rem;
  color: #4a5568;
}

@media (max-width: 640px) {
  .filters {
    flex-direction: column;
  }

  .filter-select {
    width: 100%;
  }

  .quick-stats {
    grid-template-columns: 1fr;
  }
}
.fiche-reimbursed { 
  border-left: 4px solid #00a854; /* Vert */
  background: linear-gradient(to right, rgba(0, 168, 84, 0.05), white);
}

/* Pour le badge de statut */
.status-reimbursed {
  background: #00a854; /* Vert */
  color: white;
  box-shadow: 0 2px 4px rgba(0, 168, 84, 0.2);
}

/* Pour le montant quand remboursé */
.fiche-reimbursed .montant-value {
  color: #00a854;
  font-weight: 700;
}

/* Animation pour les fiches remboursées */
.fiche-reimbursed:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 168, 84, 0.1);
}

/* Ajout d'une icône pour les fiches remboursées */
.fiche-reimbursed .status-badge::before {
  content: '✓';
  margin-right: 4px;
}
</style>