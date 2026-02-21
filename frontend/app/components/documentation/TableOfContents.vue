<template>
  <aside class="hidden xl:block w-64 flex-shrink-0 border-l border-gray-200 bg-white overflow-y-auto sticky top-16 h-[calc(100vh-4rem)]">
    <div class="p-6">
      <h3 class="text-sm font-semibold text-gray-900 mb-4">On this page</h3>
      <nav class="space-y-2">
        <a
          v-for="heading in headings"
          :key="heading.id"
          :href="`#${heading.id}`"
          class="block text-sm transition-colors"
          :class="[
            heading.level === 2 ? 'pl-0' : 'pl-4',
            activeId === heading.id ? 'text-primary-600 font-medium' : 'text-gray-600 hover:text-gray-900'
          ]"
          @click.prevent="scrollToHeading(heading.id)"
        >
          {{ heading.text }}
        </a>
      </nav>
      
      <!-- Quick Actions -->
      <div class="mt-8 pt-8 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-2">
          <button
            @click="copyCode"
            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Copy Code
          </button>
          <NuxtLink
            to="/playground"
            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Try in Playground
          </NuxtLink>
          <button
            @click="sharePage"
            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            Share
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  headings: {
    type: Array,
    default: () => []
  }
})

const activeId = ref('')

const scrollToHeading = (id) => {
  const element = document.getElementById(id)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

const copyCode = () => {
  // Find the first code block on the page
  const codeBlock = document.querySelector('pre code')
  if (codeBlock) {
    navigator.clipboard.writeText(codeBlock.textContent)
    // TODO: Show toast notification
    console.log('Code copied!')
  }
}

const sharePage = () => {
  if (navigator.share) {
    navigator.share({
      title: document.title,
      url: window.location.href
    })
  } else {
    navigator.clipboard.writeText(window.location.href)
    // TODO: Show toast notification
    console.log('Link copied!')
  }
}

// Track active heading on scroll
const updateActiveHeading = () => {
  const headings = document.querySelectorAll('h2[id], h3[id]')
  const scrollPosition = window.scrollY + 100
  
  let currentActiveId = ''
  headings.forEach((heading) => {
    if (heading.offsetTop <= scrollPosition) {
      currentActiveId = heading.id
    }
  })
  
  activeId.value = currentActiveId
}

onMounted(() => {
  window.addEventListener('scroll', updateActiveHeading)
  updateActiveHeading()
})

onUnmounted(() => {
  window.removeEventListener('scroll', updateActiveHeading)
})
</script>
