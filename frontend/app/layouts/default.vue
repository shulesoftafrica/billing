<template>
  <div class="min-h-screen flex flex-col">
    <Header />
    <main class="flex-grow">
      <slot />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Header from '~/components/layout/Header.vue'
import Footer from '~/components/layout/Footer.vue'
</script>
