<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">API Keys</h1>
      <p class="text-gray-600 mt-1">Manage your API keys for test and live environments.</p>
    </div>

    <!-- Mode Toggle -->
    <div class="mb-6 inline-flex bg-gray-100 rounded-lg p-1">
      <button
        @click="activeMode = 'test'"
        class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
        :class="activeMode === 'test' 
          ? 'bg-white text-primary-600 shadow-sm' 
          : 'text-gray-600 hover:text-gray-900'"
      >
        Test Mode
      </button>
      <button
        @click="activeMode = 'live'"
        class="px-4 py-2 text-sm font-medium rounded-md transition-colors"
        :class="activeMode === 'live' 
          ? 'bg-white text-primary-600 shadow-sm' 
          : 'text-gray-600 hover:text-gray-900'"
      >
        Live Mode
      </button>
    </div>

    <!-- Warning Banner for Live Mode -->
    <div v-if="activeMode === 'live'" class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
      <div class="flex">
        <svg class="w-5 h-5 text-amber-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <h3 class="text-sm font-medium text-amber-800">Live Mode - Handle with Care</h3>
          <p class="text-sm text-amber-700 mt-1">
            These keys can process real payments. Never share them publicly or commit them to version control.
          </p>
        </div>
      </div>
    </div>

    <!-- API Keys List -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ activeMode === 'test' ? 'Test' : 'Live' }} API Keys</h2>
            <p class="text-sm text-gray-600 mt-1">
              {{ activeMode === 'test' 
                ? 'Use these keys for development and testing with test data.' 
                : 'Use these keys for production with real payments.' 
              }}
            </p>
          </div>
          <button
            @click="showCreateModal = true"
            class="flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create New Key
          </button>
        </div>
      </div>

      <div class="divide-y divide-gray-200">
        <div
          v-for="key in filteredKeys"
          :key="key.id"
          class="p-6 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-sm font-semibold text-gray-900">{{ key.name }}</h3>
                <span 
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="key.status === 'active' 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800'"
                >
                  {{ key.status }}
                </span>
              </div>
              <div class="flex items-center space-x-2 mb-3">
                <code class="text-sm font-mono bg-gray-100 px-3 py-1 rounded">
                  {{ showKeys[key.id] ? key.key : maskKey(key.key) }}
                </code>
                <button
                  @click="toggleKeyVisibility(key.id)"
                  class="p-1 text-gray-400 hover:text-gray-600"
                  :title="showKeys[key.id] ? 'Hide' : 'Show'"
                >
                  <svg v-if="!showKeys[key.id]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
                <button
                  @click="copyKey(key.key)"
                  class="p-1 text-gray-400 hover:text-gray-600"
                  title="Copy"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                </button>
              </div>
              <div class="flex items-center text-xs text-gray-500 space-x-4">
                <span>Created: {{ key.created }}</span>
                <span>Last used: {{ key.lastUsed }}</span>
                <span v-if="key.expiresAt">Expires: {{ key.expiresAt }}</span>
              </div>
            </div>
            <div class="ml-4 flex items-center space-x-2">
              <button
                @click="openRollKeyModal(key)"
                class="px-3 py-1.5 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Roll Key
              </button>
              <button
                @click="openRevokeModal(key)"
                class="px-3 py-1.5 text-sm text-red-600 border border-red-300 rounded-lg hover:bg-red-50"
              >
                Revoke
              </button>
            </div>
          </div>
        </div>

        <div v-if="filteredKeys.length === 0" class="p-12 text-center">
          <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No API keys yet</h3>
          <p class="text-gray-600 mb-4">Create your first {{ activeMode }} API key to get started.</p>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create API Key
          </button>
        </div>
      </div>
    </div>

    <!-- Security Best Practices -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
      <h3 class="text-sm font-semibold text-blue-900 mb-3">Security Best Practices</h3>
      <ul class="space-y-2 text-sm text-blue-800">
        <li class="flex items-start">
          <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>Never share your secret API keys in publicly accessible areas like GitHub, client-side code, etc.</span>
        </li>
        <li class="flex items-start">
          <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>Use environment variables to store your API keys in your application.</span>
        </li>
        <li class="flex items-start">
          <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>Regularly rotate your API keys and revoke any unused or compromised keys.</span>
        </li>
        <li class="flex items-start">
          <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>Use test mode keys for development and only switch to live mode when ready for production.</span>
        </li>
      </ul>
    </div>

    <!-- Create API Key Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click.self="showCreateModal = false"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Create New API Key</h3>
            <button
              @click="showCreateModal = false"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="createKey">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Key Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="newKey.name"
                  type="text"
                  required
                  placeholder="e.g., Production Server"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <p class="text-xs text-gray-500 mt-1">A descriptive name to help you identify this key</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Environment
                </label>
                <select
                  v-model="newKey.environment"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="test">Test Mode</option>
                  <option value="live">Live Mode</option>
                </select>
              </div>

              <div>
                <label class="flex items-center">
                  <input
                    v-model="newKey.expiresEnabled"
                    type="checkbox"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">Set expiration date</span>
                </label>
              </div>

              <div v-if="newKey.expiresEnabled">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Expiration Date
                </label>
                <input
                  v-model="newKey.expiresAt"
                  type="date"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
              </div>
            </div>

            <div class="mt-6 flex space-x-3">
              <button
                type="button"
                @click="showCreateModal = false"
                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
              >
                Create Key
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Revoke Confirmation Modal -->
    <div
      v-if="showRevokeModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click.self="showRevokeModal = false"
    >
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Revoke API Key</h3>
              <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
            </div>
          </div>

          <p class="text-sm text-gray-700 mb-4">
            Are you sure you want to revoke <strong>{{ keyToRevoke?.name }}</strong>? 
            Any applications using this key will immediately lose access.
          </p>

          <div class="flex space-x-3">
            <button
              @click="showRevokeModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="revokeKey"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Revoke Key
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'dashboard'
})

useHead({
  title: 'API Keys - Billing Platform'
})

const activeMode = ref('test')
const showCreateModal = ref(false)
const showRevokeModal = ref(false)
const keyToRevoke = ref(null)
const showKeys = ref({})

const newKey = reactive({
  name: '',
  environment: 'test',
  expiresEnabled: false,
  expiresAt: ''
})

const apiKeys = ref([
  {
    id: 1,
    name: 'Development Server',
    key: 'bp_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    environment: 'test',
    status: 'active',
    created: 'Jan 15, 2026',
    lastUsed: '2 hours ago',
    expiresAt: null
  },
  {
    id: 2,
    name: 'Staging Environment',
    key: 'bp_test_yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy',
    environment: 'test',
    status: 'active',
    created: 'Jan 10, 2026',
    lastUsed: '1 day ago',
    expiresAt: null
  },
  {
    id: 3,
    name: 'Production Server',
    key: 'bp_live_zzzzzzzzzzzzzzzzzzzzzzzzzzzzzz',
    environment: 'live',
    status: 'active',
    created: 'Dec 20, 2025',
    lastUsed: '5 min ago',
    expiresAt: null
  },
  {
    id: 4,
    name: 'Mobile App',
    key: 'bp_live_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
    environment: 'live',
    status: 'active',
    created: 'Dec 15, 2025',
    lastUsed: '1 hour ago',
    expiresAt: 'Jun 15, 2026'
  }
])

const filteredKeys = computed(() => {
  return apiKeys.value.filter(key => key.environment === activeMode.value)
})

const maskKey = (key) => {
  const parts = key.split('_')
  if (parts.length === 3) {
    return `${parts[0]}_${parts[1]}_${'•'.repeat(24)}`
  }
  return '•'.repeat(key.length)
}

const toggleKeyVisibility = (keyId) => {
  showKeys.value[keyId] = !showKeys.value[keyId]
}

const copyKey = (key) => {
  navigator.clipboard.writeText(key)
  // Could show a toast notification here
}

const createKey = () => {
  const generatedKey = generateKey(newKey.environment)
  
  apiKeys.value.push({
    id: apiKeys.value.length + 1,
    name: newKey.name,
    key: generatedKey,
    environment: newKey.environment,
    status: 'active',
    created: 'Just now',
    lastUsed: 'Never',
    expiresAt: newKey.expiresEnabled ? newKey.expiresAt : null
  })

  showCreateModal.value = false
  newKey.name = ''
  newKey.environment = 'test'
  newKey.expiresEnabled = false
  newKey.expiresAt = ''
}

const generateKey = (env) => {
  const prefix = env === 'test' ? 'sk_test_' : 'sk_live_'
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  let key = ''
  for (let i = 0; i < 24; i++) {
    key += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return prefix + key
}

const openRevokeModal = (key) => {
  keyToRevoke.value = key
  showRevokeModal.value = true
}

const revokeKey = () => {
  if (keyToRevoke.value) {
    const index = apiKeys.value.findIndex(k => k.id === keyToRevoke.value.id)
    if (index !== -1) {
      apiKeys.value[index].status = 'revoked'
    }
  }
  showRevokeModal.value = false
  keyToRevoke.value = null
}

const openRollKeyModal = (key) => {
  // In production, this would roll (regenerate) the key
  alert('Roll key functionality - would regenerate the key with same permissions')
}
</script>
