<script setup lang="ts">
import SalesOrderView from './views/SalesOrderView.vue';
import { LayoutDashboard, ShoppingCart, Users, Package, Settings } from 'lucide-vue-next';
</script>

<template>
  <div class="flex min-h-screen bg-zoho-light">
    <main class="flex-1 overflow-auto">
      <SalesOrderView />
    </main>
  </div>
</template>

<style>
@import './style.css';
</style>
