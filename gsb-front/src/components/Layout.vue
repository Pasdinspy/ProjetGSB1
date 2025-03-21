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
  
  <style scoped>
  .layout-container {
    display: flex;
    flex-direction: row;
  }
  
  .content-container {
    flex: 1;
    padding: 2rem;
  }
  </style>