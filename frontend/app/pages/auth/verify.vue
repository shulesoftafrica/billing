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
        
        <div v-if="!verified" class="mt-8">
          <div class="mx-auto w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Check your email</h2>
          <p class="mt-2 text-gray-600">
            We've sent a verification link to
          </p>
          <p class="mt-1 text-sm font-medium text-gray-900">
            {{ email }}
          </p>
        </div>

        <div v-else class="mt-8">
          <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Email verified!</h2>
          <p class="mt-2 text-gray-600">
            Your email has been successfully verified
          </p>
        </div>
      </div>

      <!-- Verification Status -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div v-if="!verified">
          <div class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                  <p class="font-medium mb-1">Next steps:</p>
                  <ol class="list-decimal list-inside space-y-1 text-blue-700">
                    <li>Open the email we sent you</li>
                    <li>Click the verification link</li>
                    <li>Return here to continue</li>
                  </ol>
                </div>
              </div>
            </div>

            <!-- Resend Email -->
            <div class="text-center">
              <p class="text-sm text-gray-600 mb-3">
                Didn't receive the email?
              </p>
              <button
                @click="resendEmail"
                :disabled="resendCooldown > 0 || resending"
                class="inline-flex items-center px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="resending" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="resendCooldown > 0">
                  Resend in {{ resendCooldown }}s
                </span>
                <span v-else-if="resending">
                  Sending...
                </span>
                <span v-else>
                  Resend verification email
                </span>
              </button>
              
              <p v-if="resendSuccess" class="mt-2 text-sm text-green-600">
                ✓ Email sent successfully!
              </p>
            </div>

            <!-- Spam Notice -->
            <div class="text-center pt-4 border-t border-gray-200">
              <p class="text-xs text-gray-500">
                Can't find the email? Check your spam folder or contact
                <a href="mailto:support@billing.com" class="text-primary-600 hover:underline">support@billing.com</a>
              </p>
            </div>
          </div>
        </div>

        <div v-else>
          <div class="space-y-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-green-800">
                  Your account is now active and ready to use!
                </p>
              </div>
            </div>

            <NuxtLink
              to="/auth/onboarding"
              class="block w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-semibold rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all text-center"
            >
              Continue to Onboarding
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Back to Login -->
      <p class="text-center text-sm text-gray-600">
        Want to use a different email?
        <NuxtLink to="/auth/signup" class="font-medium text-primary-600 hover:text-primary-500">
          Sign up again
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
  title: 'Verify Email - Billing Platform'
})

const route = useRoute()
const email = ref(route.query.email || '')
const verified = ref(false)
const resending = ref(false)
const resendSuccess = ref(false)
const resendCooldown = ref(0)

// Check if there's a verification token in the URL
onMounted(() => {
  const token = route.query.token
  if (token) {
    verifyEmail(token)
  }
})

const verifyEmail = async (token) => {
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // In production, this would be:
    // await $fetch('/api/auth/verify-email', {
    //   method: 'POST',
    //   body: { token }
    // })
    
    verified.value = true
  } catch (error) {
    console.error('Verification error:', error)
  }
}

const resendEmail = async () => {
  if (resendCooldown.value > 0 || resending.value) {
    return
  }
  
  resending.value = true
  resendSuccess.value = false
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // In production, this would be:
    // await $fetch('/api/auth/resend-verification', {
    //   method: 'POST',
    //   body: { email: email.value }
    // })
    
    resendSuccess.value = true
    
    // Start cooldown
    resendCooldown.value = 60
    const interval = setInterval(() => {
      resendCooldown.value--
      if (resendCooldown.value <= 0) {
        clearInterval(interval)
      }
    }, 1000)
    
  } catch (error) {
    console.error('Resend error:', error)
  } finally {
    resending.value = false
  }
}
</script>
