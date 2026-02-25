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
        
        <div class="mt-8">
          <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Set new password</h2>
          <p class="mt-2 text-gray-600">
            Choose a strong password for your account
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div v-if="!resetSuccess">
          <form @submit.prevent="handleReset" class="space-y-6">
            <!-- New Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                New Password
              </label>
              <div class="relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autofocus
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

            <!-- Confirm Password -->
            <div>
              <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">
                Confirm New Password
              </label>
              <div class="relative">
                <input
                  id="confirmPassword"
                  v-model="form.confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  required
                  class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  :class="{ 'border-red-500': errors.confirmPassword }"
                  placeholder="Re-enter your password"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                >
                  <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <p v-if="errors.confirmPassword" class="mt-1 text-sm text-red-600">{{ errors.confirmPassword }}</p>
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
                Resetting password...
              </span>
              <span v-else>Reset Password</span>
            </button>
          </form>
        </div>

        <div v-else>
          <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-green-800">
                  <p class="font-medium">Password reset successfully!</p>
                  <p>You can now sign in with your new password</p>
                </div>
              </div>
            </div>

            <NuxtLink
              to="/auth/login"
              class="block w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all text-center"
            >
              Continue to Sign In
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Back to Login -->
      <div v-if="!resetSuccess" class="text-center">
        <NuxtLink to="/auth/login" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to login
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

useHead({
  title: 'Reset Password - Billing Platform'
})

const route = useRoute()
const token = ref(route.query.token || '')

const form = reactive({
  password: '',
  confirmPassword: ''
})

const errors = reactive({
  password: '',
  confirmPassword: ''
})

const loading = ref(false)
const resetSuccess = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

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
  
  errors.password = ''
  errors.confirmPassword = ''
  
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
  
  // Confirm password validation
  if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match'
    isValid = false
  }
  
  return isValid
}

const handleReset = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In production, this would be:
    // await $fetch('/api/auth/reset-password', {
    //   method: 'POST',
    //   body: {
    //     token: token.value,
    //     password: form.password
    //   }
    // })
    
    resetSuccess.value = true
    
  } catch (error) {
    console.error('Reset password error:', error)
    errors.password = 'An error occurred. The reset link may have expired.'
  } finally {
    loading.value = false
  }
}
</script>
