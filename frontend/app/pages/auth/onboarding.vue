<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-success-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <NuxtLink to="/" class="inline-block">
          <h1 class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-success-600 bg-clip-text text-transparent">
            Billing Platform
          </h1>
        </NuxtLink>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Welcome to Billing Platform</h2>
        <p class="mt-2 text-gray-600">
          Let's get you set up in just a few steps
        </p>
      </div>

      <!-- Progress Steps -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="flex items-center"
            :class="{ 'flex-1': index < steps.length - 1 }"
          >
            <div class="flex flex-col items-center">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center font-semibold transition-all"
                :class="getStepClass(index)"
              >
                <span v-if="currentStep > index" class="text-white">✓</span>
                <span v-else>{{ index + 1 }}</span>
              </div>
              <span class="mt-2 text-sm font-medium text-gray-700">{{ step.title }}</span>
            </div>
            <div
              v-if="index < steps.length - 1"
              class="flex-1 h-1 mx-4 rounded-full transition-all"
              :class="currentStep > index ? 'bg-primary-600' : 'bg-gray-200'"
            ></div>
          </div>
        </div>
      </div>

      <!-- Step Content -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <!-- Step 1: Company Information -->
        <div v-if="currentStep === 0">
          <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Company Information</h3>
            <p class="text-gray-600">Tell us about your business</p>
          </div>

          <form @submit.prevent="nextStep" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Company Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.companyName"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                  placeholder="Acme Inc."
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Industry <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="formData.industry"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                  <option value="">Select industry</option>
                  <option value="saas">SaaS</option>
                  <option value="ecommerce">E-commerce</option>
                  <option value="marketplace">Marketplace</option>
                  <option value="consulting">Consulting</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Website
              </label>
              <input
                v-model="formData.website"
                type="url"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="https://example.com"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Business Description <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="formData.description"
                required
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Describe what your business does..."
              ></textarea>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Continue
              </button>
            </div>
          </form>
        </div>

        <!-- Step 2: API Setup -->
        <div v-if="currentStep === 1">
          <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">API Setup</h3>
            <p class="text-gray-600">Get your API keys and test integration</p>
          </div>

          <div class="space-y-6">
            <!-- API Keys -->
            <div class="bg-gray-50 rounded-lg p-6">
              <h4 class="font-semibold text-gray-900 mb-4">Your API Keys</h4>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Test Mode Key (for development)
                  </label>
                  <div class="flex items-center gap-2">
                    <input
                      type="text"
                      :value="apiKeys.test"
                      readonly
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm"
                    />
                    <button
                      @click="copyToClipboard(apiKeys.test)"
                      class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      Copy
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Live Mode Key (for production)
                  </label>
                  <div class="flex items-center gap-2">
                    <input
                      :type="showLiveKey ? 'text' : 'password'"
                      :value="apiKeys.live"
                      readonly
                      class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-white font-mono text-sm"
                    />
                    <button
                      @click="showLiveKey = !showLiveKey"
                      class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                      <svg v-if="!showLiveKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                    </button>
                    <button
                      @click="copyToClipboard(apiKeys.live)"
                      class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      Copy
                    </button>
                  </div>
                </div>
              </div>

              <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-sm text-amber-800">
                  <strong>Important:</strong> Keep your live key secret. Never commit it to version control or share it publicly.
                </p>
              </div>
            </div>

            <!-- Test API Call -->
            <div>
              <h4 class="font-semibold text-gray-900 mb-3">Test Your Integration</h4>
              <p class="text-sm text-gray-600 mb-4">
                Try making your first API call to verify everything works
              </p>

              <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                <code class="text-sm text-green-400 font-mono">
                  <div>curl https://api.billing.com/v1/customers \</div>
                  <div class="ml-4">-H "Authorization: Bearer {{ apiKeys.test }}" \</div>
                  <div class="ml-4">-H "Content-Type: application/json"</div>
                </code>
              </div>

              <button
                @click="testApiCall"
                :disabled="testing"
                class="mt-4 w-full py-3 px-4 border-2 border-primary-600 text-primary-600 font-semibold rounded-lg hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
              >
                <span v-if="testing">Testing...</span>
                <span v-else>{{ apiTested ? '✓ Test Successful' : 'Test API Connection' }}</span>
              </button>
            </div>

            <!-- Next Step Buttons -->
            <div class="flex justify-between">
              <button
                @click="previousStep"
                class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50"
              >
                Back
              </button>
              <button
                @click="nextStep"
                :disabled="!apiTested"
                class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Continue
              </button>
            </div>
          </div>
        </div>

        <!-- Step 3: Go Live -->
        <div v-if="currentStep === 2">
          <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">You're All Set!</h3>
            <p class="text-gray-600">Choose how you want to proceed</p>
          </div>

          <div class="space-y-6">
            <!-- Go Live Option -->
            <div class="border-2 border-primary-500 rounded-lg p-6 bg-primary-50">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <h4 class="text-lg font-semibold text-gray-900 mb-2">Go Live Now</h4>
                  <p class="text-gray-700 mb-4">
                    Start accepting real payments immediately. Switch to live mode and integrate with your application.
                  </p>
                  <ul class="text-sm text-gray-700 space-y-2 mb-4">
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Accept real credit cards
                    </li>
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Process actual payments
                    </li>
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Full production access
                    </li>
                  </ul>
                  <button
                    @click="goLive"
                    class="w-full py-3 px-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                    Go Live
                  </button>
                </div>
              </div>
            </div>

            <!-- Continue Testing Option -->
            <div class="border-2 border-gray-300 rounded-lg p-6">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <h4 class="text-lg font-semibold text-gray-900 mb-2">Continue in Test Mode</h4>
                  <p class="text-gray-700 mb-4">
                    Keep testing with test data before going live. Perfect for development and staging environments.
                  </p>
                  <ul class="text-sm text-gray-700 space-y-2 mb-4">
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Test card numbers
                    </li>
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Safe for development
                    </li>
                    <li class="flex items-center">
                      <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      Go live anytime
                    </li>
                  </ul>
                  <button
                    @click="continueTestMode"
                    class="w-full py-3 px-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50"
                  >
                    Continue in Test Mode
                  </button>
                </div>
              </div>
            </div>

            <!-- Documentation -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <div class="text-sm text-blue-800">
                  <p class="font-medium mb-1">Need help integrating?</p>
                  <p>Check out our <NuxtLink to="/docs/quickstart" class="underline">Quick Start Guide</NuxtLink> and <NuxtLink to="/docs/examples" class="underline">Code Examples</NuxtLink></p>
                </div>
              </div>
            </div>

            <div class="flex justify-start">
              <button
                @click="previousStep"
                class="px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50"
              >
                Back
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

useHead({
  title: 'Onboarding - Billing Platform'
})

const steps = [
  { title: 'Company Info' },
  { title: 'API Setup' },
  { title: 'Go Live' }
]

const currentStep = ref(0)

const formData = reactive({
  companyName: '',
  industry: '',
  website: '',
  description: ''
})

const apiKeys = {
  test: 'sk_test_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
  live: 'sk_live_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
}

const showLiveKey = ref(false)
const testing = ref(false)
const apiTested = ref(false)

const getStepClass = (index) => {
  if (currentStep.value > index) {
    return 'bg-primary-600 text-white'
  } else if (currentStep.value === index) {
    return 'bg-primary-100 text-primary-600 border-2 border-primary-600'
  } else {
    return 'bg-gray-200 text-gray-500'
  }
}

const nextStep = () => {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
  // Could show a toast notification here
}

const testApiCall = async () => {
  testing.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    apiTested.value = true
  } catch (error) {
    console.error('API test error:', error)
  } finally {
    testing.value = false
  }
}

const goLive = () => {
  // In production, this would activate the account
  navigateTo('/dashboard')
}

const continueTestMode = () => {
  // In production, this would keep test mode active
  navigateTo('/dashboard')
}
</script>
