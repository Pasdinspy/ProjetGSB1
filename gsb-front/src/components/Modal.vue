<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <div class="modal-title">
          <slot name="header"></slot>
        </div>
        <button class="modal-close" @click="$emit('close')" title="Fermer">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="modal-content">
        <slot name="body"></slot>
      </div>
      
      <div class="modal-footer" v-if="$slots.footer">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['close'])
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}

.modal-container {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 10px 10px -5px rgba(0, 0, 0, 0.04);
  position: relative;
  animation: slideIn 0.3s ease-out;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.modal-close {
  width: 2rem;
  height: 2rem;
  border: none;
  background: transparent;
  border-radius: 9999px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background: #f1f5f9;
  color: #1e293b;
  transform: rotate(90deg);
}

.modal-content {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: #f8fafc;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .modal-container {
    background: #1e293b;
    border: 1px solid #334155;
  }

  .modal-header,
  .modal-footer {
    background: #0f172a;
    border-color: #334155;
  }

  .modal-title {
    color: #f1f5f9;
  }

  .modal-close {
    color: #94a3b8;
  }

  .modal-close:hover {
    background: #334155;
    color: #f1f5f9;
  }
}

/* Responsive design */
@media (max-width: 640px) {
  .modal-container {
    width: 95%;
    margin: 1rem;
  }

  .modal-header {
    padding: 1rem;
  }

  .modal-content {
    padding: 1rem;
  }

  .modal-footer {
    padding: 1rem;
    flex-direction: column;
  }

  .modal-footer button {
    width: 100%;
  }
}

/* Scroll customization */
.modal-content::-webkit-scrollbar {
  width: 6px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>