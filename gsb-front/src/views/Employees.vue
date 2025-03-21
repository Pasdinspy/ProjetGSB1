<template>
    <Layout>
      <div class="employee-container">
        <div class="header">
          <h1 class="title">Gestion des employés</h1>
          <button @click="openCreateModal" class="btn-add">
            <i class="fas fa-plus"></i> Nouvel employé
          </button>
        </div>
  
        <!-- Filtres -->
        <div class="filters">
          <select v-model="selectedRole" class="filter-select">
            <option value="">Tous les rôles</option>
            <option value="VISITEUR_MEDICAL">Visiteur médical</option>
            <option value="COMPTABLE">Comptable</option>
            <option value="ADMINISTRATEUR">Administrateur</option>
          </select>
          <input 
            type="text" 
            v-model="searchTerm" 
            placeholder="Rechercher..."
            class="search-input"
          >
        </div>
  
        <!-- Table des employés -->
        <div class="table-container">
          <table class="employee-table">
            <thead>
              <tr>
                <th>Nom d'utilisateur</th>
                <th>Rôle</th>
                <th v-if="showVisiteurInfo">Nom</th>
                <th v-if="showVisiteurInfo">Prénom</th>
                <th>Dernière connexion</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>{{ user.username }}</td>
                <td>
                  <span class="role-badge" :class="getRoleClass(user.role)">
                    {{ formatRole(user.role) }}
                  </span>
                </td>
                <td v-if="showVisiteurInfo">{{ user.VIS_NOM || '-' }}</td>
                <td v-if="showVisiteurInfo">{{ user.VIS_PRENOM || '-' }}</td>
                <td>{{ formatDate(user.dteConnexion) }}</td>
                <td class="actions">
                  <button @click="viewDetails(user)" class="action-button view">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button @click="editUser(user)" class="action-button edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmDelete(user)" class="action-button delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  
      <!-- Modal de création/édition -->
      <div v-if="showModal" class="modal-backdrop" @click="closeModal">
        <div class="modal-content" @click.stop>
          <h2>{{ isEditing ? 'Modifier' : 'Créer' }} un employé</h2>
          <form @submit.prevent="submitForm" class="employee-form">
            <div class="form-group">
              <label>Nom d'utilisateur</label>
              <input type="text" v-model="form.username" required>
            </div>
  
            <div class="form-group">
              <label>Mot de passe {{ isEditing ? '(laisser vide pour ne pas modifier)' : '' }}</label>
              <input type="password" v-model="form.password" :required="!isEditing">
            </div>
  
            <div class="form-group">
              <label>Rôle</label>
              <select v-model="form.role" required @change="handleRoleChange">
                <option value="VISITEUR_MEDICAL">Visiteur médical</option>
                <option value="COMPTABLE">Comptable</option>
                <option value="ADMINISTRATEUR">Administrateur</option>
              </select>
            </div>
  
            <!-- Champs spécifiques aux visiteurs médicaux -->
            <template v-if="form.role === 'VISITEUR_MEDICAL'">
              <div class="form-group">
                <label>Nom</label>
                <input type="text" v-model="form.nom" required>
              </div>
              <div class="form-group">
                <label>Prénom</label>
                <input type="text" v-model="form.prenom" required>
              </div>
              <div class="form-group">
                <label>Adresse</label>
                <input type="text" v-model="form.adresse" required>
              </div>
              <div class="form-group">
                <label>Code postal</label>
                <input type="text" v-model="form.cp" required pattern="[0-9]{5}">
              </div>
              <div class="form-group">
                <label>Ville</label>
                <input type="text" v-model="form.ville" required>
              </div>
              <div class="form-group">
                <label>Date d'embauche</label>
                <input type="date" v-model="form.date_embauche" required>
              </div>
            </template>
  
            <div class="modal-actions">
              <button type="submit" class="btn-save">
                {{ isEditing ? 'Enregistrer' : 'Créer' }}
              </button>
              <button type="button" @click="closeModal" class="btn-cancel">Annuler</button>
            </div>
          </form>
        </div>
      </div>
  
      <!-- Modal de confirmation de suppression -->
      <div v-if="userToDelete" class="modal-backdrop" @click="userToDelete = null">
        <div class="modal-content" @click.stop>
          <h2>Confirmation de suppression</h2>
          <p>Êtes-vous sûr de vouloir supprimer cet employé ?</p>
          <div class="modal-actions">
            <button @click="deleteUser" class="btn-delete">Supprimer</button>
            <button @click="userToDelete = null" class="btn-cancel">Annuler</button>
          </div>
        </div>
      </div>
  
      <!-- Modal de détails -->
      <div v-if="selectedUser" class="modal-backdrop" @click="selectedUser = null">
        <div class="modal-content details-modal" @click.stop>
          <h2>Détails de l'employé</h2>
          <div class="modal-body">
            <div class="detail-row">
              <span class="detail-label">Nom d'utilisateur:</span>
              <span class="detail-value">{{ selectedUser.username }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Rôle:</span>
              <span class="detail-value">
                <span class="role-badge" :class="getRoleClass(selectedUser.role)">
                  {{ formatRole(selectedUser.role) }}
                </span>
              </span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Dernière connexion:</span>
              <span class="detail-value">{{ formatDate(selectedUser.dteConnexion) }}</span>
            </div>
  
            <!-- Informations spécifiques aux visiteurs médicaux -->
            <template v-if="selectedUser.role === 'VISITEUR_MEDICAL'">
              <h3 class="details-subtitle">Informations visiteur médical</h3>
              <div class="detail-row">
                <span class="detail-label">Nom:</span>
                <span class="detail-value">{{ selectedUser.VIS_NOM }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Prénom:</span>
                <span class="detail-value">{{ selectedUser.VIS_PRENOM }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Adresse:</span>
                <span class="detail-value">{{ selectedUser.VIS_ADRESSE }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Code postal:</span>
                <span class="detail-value">{{ selectedUser.VIS_CP }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Ville:</span>
                <span class="detail-value">{{ selectedUser.VIS_VILLE }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Date d'embauche:</span>
                <span class="detail-value">{{ formatDate(selectedUser.VIS_DATE_EMBAUCHE) }}</span>
              </div>
            </template>
          </div>
          <button @click="selectedUser = null" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Layout>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import Layout from '../components/Layout.vue'
  
  const users = ref([])
  const selectedRole = ref('')
  const searchTerm = ref('')
  const showModal = ref(false)
  const isEditing = ref(false)
  const userToDelete = ref(null)
  const selectedUser = ref(null)
  const form = ref({
    username: '',
    password: '',
    role: 'VISITEUR_MEDICAL',
    nom: '',
    prenom: '',
    adresse: '',
    cp: '',
    ville: '',
    date_embauche: ''
  })
  
  const showVisiteurInfo = computed(() => {
    return selectedRole.value === '' || selectedRole.value === 'VISITEUR_MEDICAL'
  })
  
  const filteredUsers = computed(() => {
    return users.value.filter(user => {
      const roleMatch = !selectedRole.value || user.role === selectedRole.value
      const searchMatch = !searchTerm.value || 
        user.username.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        (user.VIS_NOM && user.VIS_NOM.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
        (user.VIS_PRENOM && user.VIS_PRENOM.toLowerCase().includes(searchTerm.value.toLowerCase()))
      return roleMatch && searchMatch
    })
  })
  
  const formatRole = (role) => {
    const roles = {
      'VISITEUR_MEDICAL': 'Visiteur médical',
      'COMPTABLE': 'Comptable',
      'ADMINISTRATEUR': 'Administrateur'
    }
    return roles[role] || role
  }
  
  const getRoleClass = (role) => {
    return {
      'role-visiteur': role === 'VISITEUR_MEDICAL',
      'role-comptable': role === 'COMPTABLE',
      'role-admin': role === 'ADMINISTRATEUR'
    }
  }
  
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
  
  const loadUsers = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/getUsers.php')
      const result = await response.json()
      if (result.success) {
        users.value = result.data
      }
    } catch (error) {
      console.error('Erreur lors du chargement des utilisateurs:', error)
    }
  }
  
  const openCreateModal = () => {
    isEditing.value = false
    form.value = {
      username: '',
      password: '',
      role: 'VISITEUR_MEDICAL',
      nom: '',
      prenom: '',
      adresse: '',
      cp: '',
      ville: '',
      date_embauche: ''
    }
    showModal.value = true
  }
  
  const editUser = (user) => {
    isEditing.value = true
    form.value = {
      id: user.id,
      username: user.username,
      password: '',
      role: user.role,
      nom: user.VIS_NOM || '',
      prenom: user.VIS_PRENOM || '',
      adresse: user.VIS_ADRESSE || '',
      cp: user.VIS_CP || '',
      ville: user.VIS_VILLE || '',
      date_embauche: user.VIS_DATE_EMBAUCHE || '',
      VIS_ID: user.VIS_ID
    }
    showModal.value = true
  }
  
  const closeModal = () => {
    showModal.value = false
    form.value = {
      username: '',
      password: '',
      role: 'VISITEUR_MEDICAL',
      nom: '',
      prenom: '',
      adresse: '',
      cp: '',
      ville: '',
      date_embauche: ''
    }
  }
  
  const handleRoleChange = () => {
    if (form.value.role !== 'VISITEUR_MEDICAL') {
      form.value.nom = ''
      form.value.prenom = ''
      form.value.adresse = ''
      form.value.cp = ''
      form.value.ville = ''
      form.value.date_embauche = ''
    }
  }
  
  const submitForm = async () => {
    try {
      const url = isEditing.value 
        ? 'http://51.83.74.206:8000/src/updateUser.php'
        : 'http://51.83.74.206:8000/src/createUser.php'
      
      const response = await fetch(url, {
        method: isEditing.value ? 'PUT' : 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(form.value)
      })
  
      const result = await response.json()
      if (result.success) {
        await loadUsers()
        closeModal()
      }
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error)
    }
  }
  
  const confirmDelete = (user) => {
    userToDelete.value = user
  }
  
  const deleteUser = async () => {
    try {
      const response = await fetch('http://51.83.74.206:8000/src/deleteUser.php', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: userToDelete.value.id })
      })
  
      const result = await response.json()
      if (result.success) {
        await loadUsers()
        userToDelete.value = null
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
    }
  }
  
  const viewDetails = (user) => {
    selectedUser.value = user
  }
  
  onMounted(() => {
    loadUsers()
  })
  </script>
  
  <style scoped>
  .employee-container {
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
    margin-bottom: 2rem;
  }
  
  .filter-select,
  .search-input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 200px;
  }
  
  .btn-add {
    background: #28a745;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
  }
  
  .employee-table {
    width: 100%;
    border-collapse: collapse;
  }
  
  .employee-table th,
  .employee-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
  }
  
  .employee-table th {
    background: #f8f9fa;
    font-weight: 600;
  }
  
  .role-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
    font-size: 0.875rem;
  }
  
  .role-visiteur {
    background: #e3f2fd;
    color: #1976d2;
  }
  
  .role-comptable {
    background: #fff3e0;
    color: #f57c00;
  }
  
  .role-admin {
    background: #fce4ec;
    color: #c2185b;
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
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
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
  
  .employee-form {
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
  
  .form-group input,
  .form-group select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
  }
  
  .btn-save,
  .btn-cancel,
  .btn-delete {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }
  
  .btn-save {
    background: #28a745;
    color: white;
  }
  
  .btn-cancel {
    background: #6c757d;
    color: white;
  }
  
  .btn-delete {
    background: #dc3545;
    color: white;
  }
  
  .details-modal {
    min-width: 500px;
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
  
  @media (max-width: 768px) {
    .header {
      flex-direction: column;
      gap: 1rem;
    }
  
    .filters {
      flex-direction: column;
    }
  
    .filter-select,
    .search-input {
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