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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Forgot password?</h2>
          <p class="mt-2 text-gray-600">
            No worries, we'll send you reset instructions
          </p>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div v-if="!emailSent">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email Address
              </label>
              <input
                id="email"
                v-model="email"
                type="email"
                required
                autofocus
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                :class="{ 'border-red-500': error }"
                placeholder="john@example.com"
              />
              <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
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
                Sending...
              </span>
              <span v-else>Send Reset Link</span>
            </button>
          </form>
        </div>

        <div v-else>
          <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-green-800">
                  <p class="font-medium mb-1">Email sent!</p>
                  <p>We've sent a password reset link to <span class="font-medium">{{ email }}</span></p>
                </div>
              </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                  <p class="font-medium mb-1">Next steps:</p>
                  <ol class="list-decimal list-inside space-y-1 text-blue-700">
                    <li>Check your email inbox</li>
                    <li>Click the reset password link</li>
                    <li>Create a new password</li>
                  </ol>
                </div>
              </div>
            </div>

            <div class="text-center">
              <p class="text-sm text-gray-600 mb-3">
                Didn't receive the email?
              </p>
              <button
                @click="handleResend"
                :disabled="resendCooldown > 0"
                class="inline-flex items-center px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="resendCooldown > 0">
                  Resend in {{ resendCooldown }}s
                </span>
                <span v-else>
                  Resend email
                </span>
              </button>
            </div>

            <div class="text-center pt-4 border-t border-gray-200">
              <p class="text-xs text-gray-500">
                Link expires in 1 hour. Check spam folder if you don't see it.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Back to Login -->
      <div class="text-center">
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
  title: 'Forgot Password - Billing Platform'
})

const email = ref('')
const loading = ref(false)
const error = ref('')
const emailSent = ref(false)
const resendCooldown = ref(0)

const handleSubmit = async () => {
  error.value = ''
  
  if (!email.value) {
    error.value = 'Email is required'
    return
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email.value)) {
    error.value = 'Please enter a valid email address'
    return
  }
  
  loading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // In production, this would be:
    // await $fetch('/api/auth/forgot-password', {
    //   method: 'POST',
    //   body: { email: email.value }
    // })
    
    emailSent.value = true
    
    // Start cooldown for resend button
    resendCooldown.value = 60
    const interval = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(interval)
      }
    }, 1000)
    
  } catch (err) {
    console.error('Forgot password error:', err)
    error.value = 'An error occurred. Please try again.'
  } finally {
    loading.value = false
  }
}

const handleResend = async () => {
  if (resendCooldown.value > 0) {
    return
  }
  
  await handleSubmit()
}
</script>
