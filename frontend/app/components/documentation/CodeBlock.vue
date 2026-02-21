<template>
  <div class="code-block-wrapper my-6">
    <div class="code-block bg-gray-950 dark:bg-black rounded-lg overflow-hidden border border-gray-800 dark:border-gray-900">
      <!-- Header -->
      <div class="code-block-header bg-gray-900 dark:bg-gray-950 border-b border-gray-800 dark:border-gray-900 px-4 py-2 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span v-if="filename" class="text-sm text-gray-400">{{ filename }}</span>
          <div v-else class="flex space-x-1">
            <button
              v-for="lang in availableLanguages"
              :key="lang.value"
              @click="selectedLanguage = lang.value"
              class="px-3 py-1 text-xs rounded transition-colors"
              :class="selectedLanguage === lang.value 
                ? 'bg-gray-700 text-white' 
                : 'text-gray-400 hover:text-gray-200'"
            >
              {{ lang.label }}
            </button>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <button
            v-if="runnable"
            @click="runCode"
            class="flex items-center space-x-1 px-3 py-1 text-xs text-primary-300 hover:text-primary-200 rounded hover:bg-gray-800 transition-colors"
          >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
            </svg>
            <span>Run</span>
          </button>
          
          <button
            @click="copyToClipboard"
            class="flex items-center space-x-1 px-3 py-1 text-xs text-gray-400 hover:text-gray-200 rounded hover:bg-gray-800 transition-colors"
          >
            <svg v-if="!copied" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ copied ? 'Copied!' : 'Copy' }}</span>
          </button>
        </div>
      </div>
      
      <!-- Code Content -->
      <pre class="text-sm overflow-x-auto p-4"><code class="text-gray-100 dark:text-gray-200">{{ displayCode }}</code></pre>
      
      <!-- Response (if runnable and executed) -->
      <div v-if="response" class="border-t border-gray-700 dark:border-gray-800 pt-4 mt-4 px-4 pb-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm text-gray-400">Response</span>
          <span :class="responseStatusClass" class="text-xs font-medium px-2 py-0.5 rounded">
            {{ response.status }}
          </span>
        </div>
        <pre class="text-sm overflow-x-auto text-success-300"><code>{{ JSON.stringify(response.data, null, 2) }}</code></pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  code: {
    type: [String, Object],
    required: true
  },
  language: {
    type: String,
    default: 'bash'
  },
  filename: {
    type: String,
    default: ''
  },
  runnable: {
    type: Boolean,
    default: false
  },
  highlightLines: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['run'])

const copied = ref(false)
const selectedLanguage = ref(props.language)
const response = ref(null)

const availableLanguages = [
  { value: 'bash', label: 'cURL' },
  { value: 'php', label: 'PHP' },
  { value: 'javascript', label: 'JavaScript' },
  { value: 'python', label: 'Python' },
]

const displayCode = computed(() => {
  if (typeof props.code === 'string') {
    return props.code
  }
  return props.code[selectedLanguage.value] || props.code.bash || ''
})

const responseStatusClass = computed(() => {
  if (!response.value) return ''
  const status = response.value.status
  if (status >= 200 && status < 300) return 'bg-success-500/20 text-success-300'
  if (status >= 400 && status < 500) return 'bg-warning-500/20 text-warning-300'
  return 'bg-error-500/20 text-error-300'
})

const copyToClipboard = () => {
  navigator.clipboard.writeText(displayCode.value)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

const runCode = async () => {
  emit('run', displayCode.value)
  
  // Simulate API response (TODO: Connect to real API)
  response.value = {
    status: 201,
    data: {
      success: true,
      message: "Resource created successfully",
      data: {
        id: 123,
        created_at: new Date().toISOString()
      }
    }
  }
}
</script>
