<template>
  <div>
    <!-- Hero Section -->
    <section class="section-padding bg-gradient-to-br from-primary-50 via-white to-success-50">
      <div class="container-custom">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
            The Payment API That <span class="text-gradient from-primary-600 to-success-600">Pays You</span>
          </h1>
          <p class="text-xl text-gray-600 mb-8">
            Earn 1% on float deposits while accepting payments. No transaction fees. No hidden charges. Go live in 5 minutes.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
            <Button to="/auth/signup" variant="primary" size="lg">
              Get Started Free
            </Button>
            <Button to="/docs" variant="outline" size="lg">
              View Documentation
            </Button>
          </div>
          
          <div class="flex items-center justify-center space-x-6 text-sm text-gray-600">
            <span class="flex items-center">
              <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              No credit card required
            </span>
            <span class="flex items-center">
              <svg class="w-5 h-5 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              2 minutes to first API call
            </span>
          </div>
          
          <!-- Code Sample -->
          <div class="mt-12 text-left code-block max-w-2xl mx-auto">
            <div class="code-block-header">
              <span class="text-sm text-gray-400">Make your first API call</span>
              <button @click="copyCode" class="text-sm text-primary-300 hover:text-primary-200">
                {{ copied ? 'Copied!' : 'Copy' }}
              </button>
            </div>
            <pre class="text-sm overflow-x-auto"><code>curl -X POST https://api.billing.com/v1/subscriptions \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"customer_id": 1, "plan_ids": [1,2]}'</code></pre>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Features Section -->
    <section class="section-padding">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Why Developers Choose Our API
          </h2>
          <p class="text-xl text-gray-600">
            Built by developers, for developers
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <Card v-for="feature in features" :key="feature.title" hover>
            <div class="text-center">
              <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary-50 flex items-center justify-center">
                <span class="text-4xl">{{ feature.icon }}</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ feature.title }}</h3>
              <p class="text-gray-600 mb-4">{{ feature.description }}</p>
              <Button :to="feature.link" variant="ghost" size="sm">
                {{ feature.cta }}
              </Button>
            </div>
          </Card>
        </div>
      </div>
    </section>
    
    <!-- Earnings Calculator Section -->
    <section class="section-padding bg-gray-50">
      <div class="container-custom">
        <div class="max-w-4xl mx-auto">
          <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">
              See How Much You'll Earn
            </h2>
            <p class="text-xl text-gray-600">
              While others charge you fees, we pay you
            </p>
          </div>
          
          <Card padding="lg">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Calculator Inputs -->
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Monthly Transaction Volume
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                    <input
                      v-model.number="transactionVolume"
                      type="number"
                      class="input pl-8"
                      placeholder="100,000"
                    />
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Average Float Duration
                  </label>
                  <select v-model="floatDuration" class="input">
                    <option :value="3">3 days</option>
                    <option :value="7">7 days</option>
                    <option :value="14">14 days</option>
                    <option :value="30">30 days</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Type
                  </label>
                  <div class="space-y-2">
                    <label v-for="type in paymentTypes" :key="type.value" class="flex items-center">
                      <input
                        v-model="paymentType"
                        type="radio"
                        :value="type.value"
                        class="mr-2"
                      />
                      <span class="text-gray-700">{{ type.label }}</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Results -->
              <div class="space-y-6">
                <div class="bg-gradient-to-br from-success-50 to-success-100 rounded-lg p-6 text-center">
                  <span class="text-2xl mb-2 block">🎉</span>
                  <p class="text-sm text-success-700 font-medium mb-2">You Earn</p>
                  <p class="text-4xl font-bold text-success-700">
                    ${{ calculatedEarnings.toFixed(2) }}<span class="text-xl">/month</span>
                  </p>
                </div>
                
                <div class="bg-gray-100 rounded-lg p-6 text-center">
                  <span class="text-2xl mb-2 block">💡</span>
                  <p class="text-sm text-gray-700 font-medium mb-2">Traditional aggregators would charge</p>
                  <p class="text-2xl font-bold text-gray-700">
                    -${{ traditionalFees.toFixed(2) }}<span class="text-base">/month</span>
                  </p>
                </div>
                
                <div class="bg-primary-50 rounded-lg p-6 text-center">
                  <span class="text-2xl mb-2 block">📈</span>
                  <p class="text-sm text-primary-700 font-medium mb-2">Your Advantage</p>
                  <p class="text-3xl font-bold text-primary-700">
                    ${{ totalAdvantage.toFixed(2) }}<span class="text-lg"> saved!</span>
                  </p>
                </div>
                
                <Button to="/auth/signup" variant="success" size="lg" full-width>
                  Get Started →
                </Button>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="section-padding">
      <div class="container-custom">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Get Started in 4 Simple Steps
          </h2>
          <p class="text-xl text-gray-600">
            Average time: 4 minutes 15 seconds
          </p>
        </div>
        
        <div class="max-w-3xl mx-auto space-y-8">
          <div v-for="(step, index) in steps" :key="index" class="flex gap-6">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center text-xl font-bold">
                {{ index + 1 }}
              </div>
            </div>
            <div class="flex-grow">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                {{ step.title }}
              </h3>
              <p class="text-gray-600 mb-2">{{ step.description }}</p>
              <p class="text-sm text-gray-500">⏱️ {{ step.time }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Social Proof Section -->
    <section class="section-padding bg-primary-600 text-white">
      <div class="container-custom">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold mb-4">
            Trusted by Developers, Loved by Startups
          </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
          <div v-for="testimonial in testimonials" :key="testimonial.author" class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
            <p class="text-lg mb-4">"{{ testimonial.quote }}"</p>
            <div class="flex items-center">
              <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold mr-3">
                {{ testimonial.initials }}
              </div>
              <div>
                <p class="font-semibold">{{ testimonial.author }}</p>
                <p class="text-sm text-primary-100">{{ testimonial.role }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex flex-wrap justify-center gap-8 text-center">
          <div>
            <p class="text-4xl font-bold mb-2">5,000+</p>
            <p class="text-primary-100">Developers</p>
          </div>
          <div>
            <p class="text-4xl font-bold mb-2">1M+</p>
            <p class="text-primary-100">Transactions</p>
          </div>
          <div>
            <p class="text-4xl font-bold mb-2">$50K+</p>
            <p class="text-primary-100">Paid to Developers</p>
          </div>
        </div>
      </div>
    </section>
    
    <!-- CTA Section -->
    <section class="section-padding">
      <div class="container-custom">
        <div class="max-w-3xl mx-auto text-center">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Ready to Start Earning?
          </h2>
          <p class="text-xl text-gray-600 mb-8">
            Join thousands of developers who chose to get paid instead of paying transaction fees.
          </p>
          <Button to="/auth/signup" variant="primary" size="lg">
            Create Free Account
          </Button>
          <p class="text-sm text-gray-500 mt-4">
            No credit card • 2 min setup • Start earning today
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Button from '~/components/shared/Button.vue'
import Card from '~/components/shared/Card.vue'

useHead({
  title: 'Home'
})

const copied = ref(false)
const transactionVolume = ref(100000)
const floatDuration = ref(7)
const paymentType = ref('subscriptions')

const paymentTypes = [
  { value: 'subscriptions', label: 'Subscriptions' },
  { value: 'one-time', label: 'One-time payments' },
  { value: 'mixed', label: 'Mixed' },
]

const features = [
  {
    icon: '💰',
    title: 'We Pay You',
    description: 'Earn 1% on float while competitors charge you per transaction.',
    cta: 'Learn More',
    link: '/pricing'
  },
  {
    icon: '⚡',
    title: 'Go Live in Minutes',
    description: 'No waiting. No approval delays. Get API keys in seconds.',
    cta: 'Try Now',
    link: '/auth/signup'
  },
  {
    icon: '🎯',
    title: 'Self-Service',
    description: 'Upload KYC, test, and go live at your own pace.',
    cta: 'See How',
    link: '/docs/quickstart'
  },
  {
    icon: '🛠️',
    title: 'Powerful Dashboard',
    description: 'Test APIs, monitor in real-time, manage everything.',
    cta: 'Explore',
    link: '/docs'
  },
  {
    icon: '📚',
    title: 'Best in Class Docs',
    description: 'Interactive examples, code in 7 languages.',
    cta: 'Read Docs',
    link: '/docs'
  },
  {
    icon: '🔒',
    title: 'Bank-Grade Security',
    description: 'PCI DSS compliant, encrypted, audited.',
    cta: 'Security',
    link: '/security'
  },
]

const steps = [
  {
    title: '📝 Sign Up (30 seconds)',
    description: 'Email, password, done. No credit card required.',
    time: '30 seconds'
  },
  {
    title: '📄 Upload KYC (2 minutes)',
    description: 'Drag & drop documents. We verify instantly.',
    time: '2 minutes'
  },
  {
    title: '🧪 Test Integration (2 minutes)',
    description: 'Use sandbox. See live responses.',
    time: '2 minutes'
  },
  {
    title: '🚀 Go Live (You decide!)',
    description: 'Flip the switch when ready. No approval needed.',
    time: 'Your choice'
  },
]

const testimonials = [
  {
    quote: "Changed my game. Making $500/month on float I never knew I could earn.",
    author: "Sarah K.",
    role: "SaaS Founder",
    initials: "SK"
  },
  {
    quote: "Went live in 3min. No hassle, no waiting. Best API experience ever.",
    author: "James M.",
    role: "Mobile App Dev",
    initials: "JM"
  },
  {
    quote: "Making $1.2K monthly Extra! This is revolutionary for small businesses.",
    author: "Alex T.",
    role: "Startup CTO",
    initials: "AT"
  },
]

// Calculator logic
const calculatedEarnings = computed(() => {
  // Simplified calculation: 1% earnings on float
  // Assuming 5% annual interest rate on deposits
  const annualRate = 0.05
  const dailyRate = annualRate / 365
  return transactionVolume.value * dailyRate * floatDuration.value
})

const traditionalFees = computed(() => {
  // Typical payment gateway fees are 2.5%
  return transactionVolume.value * 0.025
})

const totalAdvantage = computed(() => {
  return calculatedEarnings.value + traditionalFees.value
})

const copyCode = () => {
  const code = `curl -X POST https://api.billing.com/v1/subscriptions \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -d '{"customer_id": 1, "plan_ids": [1,2]}'`
  
  navigator.clipboard.writeText(code)
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}
</script>
