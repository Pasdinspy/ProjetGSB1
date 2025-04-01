Waiting for reply…

Je vais améliorer le CSS du Modal pour le rendre plus attractif et moderne. Voici la version améliorée :
Vue

<template>
  <div class="gsb-modal-backdrop" @click="$emit('close')">
    <div class="gsb-modal-content" @click.stop>
      <div class="gsb-modal-header">
        <slot name="header"></slot>
        <button class="gsb-modal-close" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="gsb-modal-body">
        <slot name="body"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['close'])
</script>
