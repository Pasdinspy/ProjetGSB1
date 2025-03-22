<template>
  <div class="layout-container">
    <Sidebar />
    <div class="content-container">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'
</script>