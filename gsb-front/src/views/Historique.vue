<template>
  <Layout>
    <div class="historique-container">
      <div class="header">
        <h1 class="title">Historique des fiches de frais</h1>
        <div class="filters">
          <select v-model="selectedMonth" class="filter-select">
            <option value="">Tous les mois</option>
            <option v-for="month in months" :key="month" :value="month">
              {{ month }}
            </option>
          </select>
          <select v-model="selectedYear" class="filter-select">
            <option value="">Toutes les années</option>
            <option v-for="year in years" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
          <select v-model="selectedStatus" class="filter-select">
            <option value="">Tous les états</option>
            <option value="CR">En cours</option>
            <option value="VA">Validée</option>
            <option value="RE">Remboursée</option>
          </select>
        </div>
      </div>

      <div class="table-container">
        <table v-if="filteredFiches.length" class="fiche-table">
          <thead>
            <tr>
              <th v-if="isAdmin">Visiteur</th>
              <th>Période</th>
              <th>Montant</th>
              <th>Justificatifs</th>
              <th>État</th>
              <th>Date modification</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="fiche in filteredFiches" :key="fiche.FFR_ID" 
                :class="{ 'validated': fiche.ETA_ID === 'VA', 'pending': fiche.ETA_ID === 'CR' }">
              <td v-if="isAdmin">{{ fiche.VIS_NOM }} {{ fiche.VIS_PRENOM }}</td>
              <td>{{ fiche.FFR_MOIS }} {{ fiche.FFR_ANNEE }}</td>
              <td>{{ formatMontant(fiche.FFR_MONTANT_VALIDE) }} €</td>
              <td>{{ fiche.FFR_NB_JUSTIFICATIFS }}</td>
              <td>
                <span class="status-badge" :class="getStatusClass(fiche.ETA_ID)">
                  {{ fiche.status }}
                </span>
              </td>
              <td>{{ formatDate(fiche.FFR_DATE_MODIF) }}</td>
              <td class="actions">
                <button @click="viewDetails(fiche)" class="action-button view" title="Voir les détails">
                  <i class="fas fa-eye"></i>
                </button>
                <button v-if="fiche.ETA_ID === 'CR'" 
                        @click="editFiche(fiche)" 
                        class="action-button edit"
                        title="Modifier">
                  <i class="fas fa-edit"></i>
                </button>
                <button v-if="canDelete(fiche)"
                        @click="confirmDelete(fiche)"
                        class="action-button delete"
                        title="Supprimer">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-state">
          <i class="fas fa-folder-open"></i>
          <p>Aucune fiche de frais trouvée</p>
        </div>
      </div>
    </div>

    <!-- Modal d'édition -->
    <div v-if="editingFiche" class="modal-backdrop" @click="editingFiche = null">
      <div class="modal-content edit-modal" @click.stop>
        <h2>Modifier la fiche de frais</h2>
        <form @submit.prevent="updateFiche" class="edit-form">
          <div class="form-group">
            <label>Repas</label>
            <input type="number" v-model="editForm.repas" min="0" required>
          </div>
          <div class="form-group">
            <label>Nuitées</label>
            <input type="number" v-model="editForm.nuitees" min="0" required>
          </div>
          <div class="form-group">
            <label>Étapes</label>
            <input type="number" v-model="editForm.etape" min="0" required>
          </div>
          <div class="form-group">
            <label>Kilométrage</label>
            <input type="number" v-model="editForm.km" min="0" required>
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn-save">Enregistrer</button>
            <button type="button" @click="editingFiche = null" class="btn-cancel">Annuler</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div v-if="ficheToDelete" class="modal-backdrop" @click="ficheToDelete = null">
      <div class="modal-content" @click.stop>
        <h2>Confirmation de suppression</h2>
        <p>Êtes-vous sûr de vouloir supprimer cette fiche de frais ?</p>
        <div class="modal-actions">
          <button @click="deleteFiche" class="btn-delete">Supprimer</button>
          <button @click="ficheToDelete = null" class="btn-cancel">Annuler</button>
        </div>
      </div>
    </div>

    <!-- Modal de détails -->
    <div v-if="selectedFiche" class="modal-backdrop" @click="selectedFiche = null">
      <div class="modal-content details-modal" @click.stop>
        <h2>Détails de la fiche de frais</h2>
        <div class="modal-body">
          <div class="detail-row">
            <span class="detail-label">Visiteur:</span>
            <span class="detail-value">{{ selectedFiche.VIS_NOM }} {{ selectedFiche.VIS_PRENOM }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Période:</span>
            <span class="detail-value">{{ selectedFiche.FFR_MOIS }} {{ selectedFiche.FFR_ANNEE }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Montant total:</span>
            <span class="detail-value">{{ formatMontant(selectedFiche.FFR_MONTANT_VALIDE) }} €</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">État:</span>
            <span class="detail-value">
              <span class="status-badge" :class="getStatusClass(selectedFiche.ETA_ID)">
                {{ selectedFiche.status }}
              </span>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Date de modification:</span>
            <span class="detail-value">{{ formatDate(selectedFiche.FFR_DATE_MODIF) }}</span>
          </div>
          
          <!-- Détails des frais -->
          <h3 class="details-subtitle">Détail des frais forfaitaires</h3>
          <div class="frais-details">
            <div class="frais-item">
              <span class="frais-label">Repas</span>
              <span class="frais-value">{{ selectedFiche.repas || 0 }}</span>
            </div>
            <div class="frais-item">
              <span class="frais-label">Nuitées</span>
              <span class="frais-value">{{ selectedFiche.nuitees || 0 }}</span>
            </div>
            <div class="frais-item">
              <span class="frais-label">Étapes</span>
              <span class="frais-value">{{ selectedFiche.etape || 0 }}</span>
            </div>
            <div class="frais-item">
              <span class="frais-label">Kilométrage</span>
              <span class="frais-value">{{ selectedFiche.km || 0 }} km</span>
            </div>
          </div>
        </div>
        <button @click="selectedFiche = null" class="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import Layout from '../components/Layout.vue'

const authStore = useAuthStore()
const fiches = ref([])
const years = ref([])
const selectedFiche = ref(null)
const ficheToDelete = ref(null)
const editingFiche = ref(null)
const selectedMonth = ref('')
const selectedYear = ref('')
const selectedStatus = ref('')
const editForm = ref({
  repas: 0,
  nuitees: 0,
  etape: 0,
  km: 0
})

const isAdmin = computed(() => authStore.isAdministrateur())

const months = [
  'JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN',
  'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'
]

const filteredFiches = computed(() => {
  return fiches.value.filter(fiche => {
    const monthMatch = !selectedMonth.value || fiche.FFR_MOIS === selectedMonth.value
    const yearMatch = !selectedYear.value || fiche.FFR_ANNEE === selectedYear.value
    const statusMatch = !selectedStatus.value || fiche.ETA_ID === selectedStatus.value
    return monthMatch && yearMatch && statusMatch
  })
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
    'VA': 'status-validated',
    'RE': 'status-reimbursed'
  }
  return statusClasses[status] || 'status-default'
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

const loadFicheFraisForfait = async (ficheId) => {
  try {
    const response = await fetch(`http://51.83.74.206:8000/src/getFraisForfait.php?FFR_ID=${ficheId}`)
    const result = await response.json()
    
    if (result.success) {
      return result.data
    }
    return null
  } catch (error) {
    console.error('Erreur lors du chargement des frais forfaits:', error)
    return null
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
    } else {
      console.error('Erreur lors du chargement des frais forfaits:', result.message)
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
    } else {
      console.error('Erreur lors du chargement des frais forfaits:', result.message)
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
    } else {
      console.error('Erreur lors de la mise à jour:', result.message)
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
  }
}

const canDelete = (fiche) => {
  return fiche.ETA_ID === 'CR' && 
         (isAdmin.value || authStore.user.VIS_ID === fiche.VIS_ID)
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
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.title {
  font-size: 1.5rem;
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
  background: white;
}

.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.fiche-table {
  width: 100%;
  border-collapse: collapse;
}

.fiche-table th,
.fiche-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.fiche-table th {
  background: #f8f9fa;
  font-weight: 600;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 999px;
  font-size: 0.875rem;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-validated {
  background: #d4edda;
  color: #155724;
}

.status-reimbursed {
  background: #cce5ff;
  color: #004085;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.action-button {
  padding: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-button:hover {
  transform: translateY(-2px);
}

.action-button.view {
  background: #e3f2fd;
  color: #1976d2;
}

.action-button.edit {
  background: #fff3e0;
  color: #f57c00;
}

.action-button.delete {
  background: #ffebee;
  color: #d32f2f;
}

.empty-state {
  padding: 3rem;
  text-align: center;
  color: #6c757d;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  min-width: 400px;
  position: relative;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-delete,
.btn-cancel {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-delete {
  background: #dc3545;
  color: white;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-save {
  background: #28a745;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.details-modal {
  min-width: 500px;
}

.detail-row {
  margin-bottom: 1rem;
  display: flex;
  border-bottom: 1px solid #eee;
  padding: 0.5rem 0;
}

.detail-label {
  font-weight: bold;
  min-width: 150px;
  color: #666;
}

.detail-value {
  color: #2c3e50;
}

.details-subtitle {
  margin: 1.5rem 0 1rem;
  color: #2c3e50;
  border-bottom: 2px solid #eee;
  padding-bottom: 0.5rem;
}

.frais-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.frais-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 4px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.frais-label {
  color: #666;
  font-weight: 500;
}

.frais-value {
  color: #2c3e50;
  font-weight: 600;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #2c3e50;
}

.form-group input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
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

  .modal-content {
    margin: 1rem;
    min-width: auto;
    width: calc(100% - 2rem);
  }

  .actions {
    flex-wrap: wrap;
  }

  .action-button {
    padding: 0.25rem;
  }
}
</style>