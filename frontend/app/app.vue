<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
// Global app configuration
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} - Billing API` : 'Billing API - The Payment API That Pays You'
  }
})
</script>
