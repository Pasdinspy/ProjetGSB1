<template>
    <Layout>
      <div class="payment-container">
        <div class="header">
          <h1 class="title">Gestion des paiements</h1>
          <div class="header-actions">
            <div class="filters">
              <select v-model="selectedState" class="filter-select">
                <option value="">Tous les états</option>
                <option value="EN_ATTENTE">En attente</option>
                <option value="VALIDE">Validé</option>
                <option value="REFUSE">Refusé</option>
              </select>
              <select v-model="selectedVisitor" class="filter-select">
                <option value="">Tous les visiteurs</option>
                <option v-for="visitor in visitors" 
                        :key="visitor.VIS_ID" 
                        :value="visitor.VIS_ID">
                  {{ visitor.VIS_NOM }} {{ visitor.VIS_PRENOM }}
                </option>
              </select>
              <input 
                type="month" 
                v-model="selectedMonth"
                class="month-select"
              >
            </div>
          </div>
        </div>
  
        <!-- Table des paiements -->
        <div class="table-container">
          <table class="payment-table">
            <thead>
              <tr>
                <th>Date demande</th>
                <th>Visiteur</th>
                <th>Montant</th>
                <th>État</th>
                <th>Date validation</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="payment in filteredPayments" :key="payment.id">
                <td>{{ formatDate(payment.dateCreation) }}</td>
                <td>{{ payment.visitorName }}</td>
                <td>{{ formatAmount(payment.montant) }} €</td>
                <td>
                  <span class="status-badge" :class="getStatusClass(payment.etat)">
                    {{ formatStatus(payment.etat) }}
                  </span>
                </td>
                <td>{{ formatDate(payment.dateValidation) || '-' }}</td>
                <td class="actions">
                  <button 
                    v-if="payment.etat === 'EN_ATTENTE'" 
                    @click="validatePayment(payment)" 
                    class="action-button validate"
                    title="Valider">
                    <i class="fas fa-check"></i>
                  </button>
                  <button 
                    v-if="payment.etat === 'EN_ATTENTE'" 
                    @click="rejectPayment(payment)" 
                    class="action-button reject"
                    title="Refuser">
                    <i class="fas fa-times"></i>
                  </button>
                  <button 
                    @click="viewDetails(payment)" 
                    class="action-button view"
                    title="Voir détails">
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredPayments.length === 0">
                <td colspan="6" class="no-data">
                  Aucun paiement trouvé
                </td>
              </tr>
            </tbody>
          </table>
        </div>
  
        <!-- Modal de détails -->
        <div v-if="selectedPayment" class="modal-backdrop" @click="selectedPayment = null">
          <div class="modal-content details-modal" @click.stop>
            <h2>Détails du paiement</h2>
            <div class="modal-body">
              <div class="detail-row">
                <span class="detail-label">Date de demande:</span>
                <span class="detail-value">{{ formatDate(selectedPayment.dateCreation) }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Visiteur:</span>
                <span class="detail-value">{{ selectedPayment.visitorName }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Montant:</span>
                <span class="detail-value">{{ formatAmount(selectedPayment.montant) }} €</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">État:</span>
                <span class="detail-value">
                  <span class="status-badge" :class="getStatusClass(selectedPayment.etat)">
                    {{ formatStatus(selectedPayment.etat) }}
                  </span>
                </span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date de validation:</span>
                <span class="detail-value">{{ formatDate(selectedPayment.dateValidation) || '-' }}</span>
              </div>
              <div v-if="selectedPayment.commentaire" class="detail-row">
                <span class="detail-label">Commentaire:</span>
                <span class="detail-value">{{ selectedPayment.commentaire }}</span>
              </div>
              
              <!-- Section des frais -->
              <h3 class="details-subtitle">Frais associés</h3>
              <div class="expenses-table">
                <table>
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Type</th>
                      <th>Montant</th>
                      <th>Justificatif</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="expense in selectedPayment.frais" :key="expense.id">
                      <td>{{ formatDate(expense.date) }}</td>
                      <td>{{ expense.type }}</td>
                      <td>{{ formatAmount(expense.montant) }} €</td>
                      <td>
                        <a v-if="expense.justificatif" 
                           :href="expense.justificatif" 
                           target="_blank"
                           class="justificatif-link">
                          <i class="fas fa-file-pdf"></i>
                        </a>
                        <span v-else>-</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-actions" v-if="selectedPayment.etat === 'EN_ATTENTE'">
              <button @click="validatePayment(selectedPayment)" class="btn-validate">
                Valider
              </button>
              <button @click="rejectPayment(selectedPayment)" class="btn-reject">
                Refuser
              </button>
            </div>
            <button @click="selectedPayment = null" class="modal-close">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
  
        <!-- Modal de confirmation de rejet -->
        <div v-if="showRejectModal" class="modal-backdrop" @click="closeRejectModal">
          <div class="modal-content" @click.stop>
            <h2>Confirmation de rejet</h2>
            <div class="form-group">
              <label>Motif du rejet :</label>
              <textarea 
                v-model="rejectComment" 
                rows="4" 
                placeholder="Veuillez indiquer le motif du rejet..."
                required
              ></textarea>
            </div>
            <div class="modal-actions">
              <button 
                @click="confirmReject" 
                class="btn-reject" 
                :disabled="!rejectComment.trim()">
                Confirmer le rejet
              </button>
              <button @click="closeRejectModal" class="btn-cancel">
                Annuler
              </button>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import Layout from '../components/Layout.vue'
  
  // États
  const payments = ref([])
  const visitors = ref([])
  const selectedState = ref('')
  const selectedVisitor = ref('')
  const selectedMonth = ref('')
  const selectedPayment = ref(null)
  const showRejectModal = ref(false)
  const rejectComment = ref('')
  const paymentToReject = ref(null)
  
  // Initialisation du mois courant
  const currentDate = new Date()
  selectedMonth.value = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`
  
  // Filtrage des paiements
  const filteredPayments = computed(() => {
    return payments.value.filter(payment => {
      const stateMatch = !selectedState.value || payment.etat === selectedState.value
      const visitorMatch = !selectedVisitor.value || payment.VIS_ID === selectedVisitor.value
      const monthMatch = !selectedMonth.value || payment.dateCreation.startsWith(selectedMonth.value)
      return stateMatch && visitorMatch && monthMatch
    })
  })
  
  // Formatage
  const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('fr-FR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
  
  const formatAmount = (amount) => {
    return Number(amount).toLocaleString('fr-FR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }
  
  const formatStatus = (status) => {
    const statuses = {
      'EN_ATTENTE': 'En attente',
      'VALIDE': 'Validé',
      'REFUSE': 'Refusé'
    }
    return statuses[status] || status
  }
  
  const getStatusClass = (status) => {
    return {
      'status-pending': status === 'EN_ATTENTE',
      'status-validated': status === 'VALIDE',
      'status-rejected': status === 'REFUSE'
    }
  }
  
  // Actions
  const loadPayments = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/getPayments.php')
      const result = await response.json()
      if (result.success) {
        payments.value = result.data
      }
    } catch (error) {
      console.error('Erreur lors du chargement des paiements:', error)
    }
  }
  
  const loadVisitors = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/getVisitors.php')
      const result = await response.json()
      if (result.success) {
        visitors.value = result.data
      }
    } catch (error) {
      console.error('Erreur lors du chargement des visiteurs:', error)
    }
  }
  
  const viewDetails = (payment) => {
    selectedPayment.value = payment
  }
  
  const validatePayment = async (payment) => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/validatePayment.php', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: payment.id })
      })
      
      const result = await response.json()
      if (result.success) {
        await loadPayments()
        selectedPayment.value = null
      }
    } catch (error) {
      console.error('Erreur lors de la validation du paiement:', error)
    }
  }
  
  const rejectPayment = (payment) => {
    paymentToReject.value = payment
    rejectComment.value = ''
    showRejectModal.value = true
  }
  
  const closeRejectModal = () => {
    showRejectModal.value = false
    paymentToReject.value = null
    rejectComment.value = ''
  }
  
  const confirmReject = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/rejectPayment.php', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: paymentToReject.value.id,
          commentaire: rejectComment.value
        })
      })
      
      const result = await response.json()
      if (result.success) {
        await loadPayments()
        closeRejectModal()
        selectedPayment.value = null
      }
    } catch (error) {
      console.error('Erreur lors du rejet du paiement:', error)
    }
  }
  
  onMounted(() => {
    loadPayments()
    loadVisitors()
  })
  </script>
  
  <style scoped>
  .payment-container {
    padding: 2rem;
  }
  
  .header {
    margin-bottom: 2rem;
  }
  
  .title {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 1rem;
  }
  
  .header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
  }
  
  .filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }
  
  .filter-select,
  .month-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
  }
  
  .table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
  }
  
  .payment-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .payment-table th,
  .payment-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
  }
  
  .payment-table th {
    background: #f8f9fa;
    font-weight: 600;
  }
  
  .status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
    font-size: 0.875rem;
    white-space: nowrap;
  }
  
  .status-pending {
    background: #fff3e0;
    color: #f57c00;
  }
  
  .status-validated {
    background: #e8f5e9;
    color: #2e7d32;
  }
  
  .status-rejected {
    background: #ffebee;
    color: #c62828;
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
  
  .action-button.validate {
    background: #e8f5e9;
    color: #2e7d32;
  }
  
  .action-button.reject {
    background: #ffebee;
    color: #c62828;
  }
  
  .action-button.view {
    background: #e3f2fd;
    color: #1976d2;
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
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
  }
  
  .details-modal {
    min-width: 600px;
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
  
  .details-subtitle {
    margin: 1.5rem 0 1rem;
    color: #2c3e50;
    border-bottom: 2px solid #eee;
    padding-bottom: 0.5rem;
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
  
  .expenses-table {
    margin-top: 1rem;
  }
  
  .expenses-table table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .expenses-table th,
  .expenses-table td {
    padding: 0.75rem;
    border: 1px solid #eee;
  }
  
  .expenses-table th {
    background: #f8f9fa;
    font-weight: 600;
  }
  
  .justificatif-link {
    color: #1976d2;
    text-decoration: none;
  }
  
  .justificatif-link:hover {
    text-decoration: underline;
  }
  
  .form-group {
    margin-bottom: 1rem;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }
  
  .form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
  }
  
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
  }
  
  .btn-validate,
  .btn-reject,
  .btn-cancel {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
  }
  
  .btn-validate {
    background: #2e7d32;
    color: white;
  }
  
  .btn-reject {
    background: #c62828;
    color: white;
  }
  
  .btn-reject:disabled {
    background: #cccccc;
    cursor: not-allowed;
  }
  
  .btn-cancel {
    background: #6c757d;
    color: white;
  }
  
  .no-data {
    text-align: center;
    color: #666;
    padding: 2rem;
  }
  
  @media (max-width: 768px) {
    .filters {
      flex-direction: column;
    }
  
    .filter-select,
    .month-select {
      width: 100%;
    }
  
    .modal-content {
      margin: 1rem;
      min-width: auto;
      width: calc(100% - 2rem);
    }
  
    .details-modal {
      min-width: auto;
    }
  
    .actions {
      flex-wrap: wrap;
    }
  
    .action-button {
      padding: 0.25rem;
    }
  }
  </style>