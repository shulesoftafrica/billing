<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <DashboardSidebar 
      :sidebarOpen="sidebarOpen" 
      @close="sidebarOpen = false" 
    />

    <!-- Main Content Area -->
    <div class="lg:pl-64">
      <!-- Top Navigation -->
      <DashboardHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Page Content -->
      <main class="py-6 px-4 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
const sidebarOpen = ref(false)
</script>
