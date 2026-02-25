<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-success-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <NuxtLink to="/" class="inline-block">
          <h1 class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-success-600 bg-clip-text text-transparent">
            Billing Platform
          </h1>
        </NuxtLink>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">Create your account</h2>
        <p class="mt-2 text-sm text-gray-600">
          Start accepting payments in minutes
        </p>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="handleSignup" class="space-y-6">
          <!-- Full Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
              Full Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.name }"
              placeholder="John Doe"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.email }"
              placeholder="john@example.com"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Company Name -->
          <div>
            <label for="company" class="block text-sm font-medium text-gray-700 mb-1">
              Company Name
            </label>
            <input
              id="company"
              v-model="form.company"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.company }"
              placeholder="Acme Inc."
            />
            <p v-if="errors.company" class="mt-1 text-sm text-red-600">{{ errors.company }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.password }"
                placeholder="Min. 8 characters"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            
            <!-- Password Strength Indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="flex items-center gap-2 mb-1">
                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-full transition-all duration-300"
                    :class="passwordStrengthClass"
                    :style="{ width: `${passwordStrength}%` }"
                  ></div>
                </div>
                <span class="text-xs font-medium" :class="passwordStrengthTextClass">
                  {{ passwordStrengthText }}
                </span>
              </div>
              <ul class="text-xs text-gray-600 space-y-1">
                <li :class="{ 'text-green-600': form.password.length >= 8 }">
                  ✓ At least 8 characters
                </li>
                <li :class="{ 'text-green-600': /[A-Z]/.test(form.password) }">
                  ✓ One uppercase letter
                </li>
                <li :class="{ 'text-green-600': /[0-9]/.test(form.password) }">
                  ✓ One number
                </li>
              </ul>
            </div>
          </div>

          <!-- Terms -->
          <div class="flex items-start">
            <input
              id="terms"
              v-model="form.acceptTerms"
              type="checkbox"
              required
              class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label for="terms" class="ml-2 text-sm text-gray-600">
              I agree to the
              <NuxtLink to="/terms" class="text-primary-600 hover:underline">Terms of Service</NuxtLink>
              and
              <NuxtLink to="/privacy" class="text-primary-600 hover:underline">Privacy Policy</NuxtLink>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Creating account...
            </span>
            <span v-else>Create Account</span>
          </button>
        </form>

        <!-- Social Login -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-3">
            <button
              type="button"
              class="w-full inline-flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Google
            </button>

            <button
              type="button"
              class="w-full inline-flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
              </svg>
              GitHub
            </button>
          </div>
        </div>
      </div>

      <!-- Sign In Link -->
      <p class="text-center text-sm text-gray-600">
        Already have an account?
        <NuxtLink to="/auth/login" class="font-medium text-primary-600 hover:text-primary-500">
          Sign in
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

useHead({
  title: 'Sign Up - Billing Platform'
})

const form = reactive({
  name: '',
  email: '',
  company: '',
  password: '',
  acceptTerms: false
})

const errors = reactive({
  name: '',
  email: '',
  company: '',
  password: ''
})

const loading = ref(false)
const showPassword = ref(false)

const passwordStrength = computed(() => {
  let strength = 0
  if (form.password.length >= 8) strength += 33
  if (/[A-Z]/.test(form.password)) strength += 33
  if (/[0-9]/.test(form.password)) strength += 34
  return strength
})

const passwordStrengthClass = computed(() => {
  if (passwordStrength.value < 33) return 'bg-red-500'
  if (passwordStrength.value < 66) return 'bg-yellow-500'
  return 'bg-green-500'
})

const passwordStrengthTextClass = computed(() => {
  if (passwordStrength.value < 33) return 'text-red-600'
  if (passwordStrength.value < 66) return 'text-yellow-600'
  return 'text-green-600'
})

const passwordStrengthText = computed(() => {
  if (passwordStrength.value < 33) return 'Weak'
  if (passwordStrength.value < 66) return 'Medium'
  return 'Strong'
})

const validateForm = () => {
  let isValid = true
  
  // Reset errors
  errors.name = ''
  errors.email = ''
  errors.company = ''
  errors.password = ''
  
  // Name validation
  if (!form.name || form.name.length < 2) {
    errors.name = 'Name must be at least 2 characters'
    isValid = false
  }
  
  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.email || !emailRegex.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }
  
  // Company validation
  if (!form.company || form.company.length < 2) {
    errors.company = 'Company name must be at least 2 characters'
    isValid = false
  }
  
  // Password validation
  if (!form.password || form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    isValid = false
  } else if (!/[A-Z]/.test(form.password)) {
    errors.password = 'Password must contain at least one uppercase letter'
    isValid = false
  } else if (!/[0-9]/.test(form.password)) {
    errors.password = 'Password must contain at least one number'
    isValid = false
  }
  
  return isValid
}

const handleSignup = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In production, this would be:
    // const response = await $fetch('/api/auth/signup', {
    //   method: 'POST',
    //   body: {
    //     name: form.name,
    //     email: form.email,
    //     company: form.company,
    //     password: form.password
    //   }
    // })
    
    // Redirect to email verification
    navigateTo('/auth/verify?email=' + encodeURIComponent(form.email))
  } catch (error) {
    console.error('Signup error:', error)
    errors.email = 'An error occurred. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>
