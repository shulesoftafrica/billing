<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Support</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="support">Support</h1>
      <p class="text-xl text-gray-600">
        Get help from our developer support team and community.
      </p>
    </div>
    
    <!-- Contact Options -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6" id="contact">Contact Options</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border-2 border-primary-500 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Email Support</h3>
              <p class="text-sm text-gray-600">Get help via email</p>
            </div>
          </div>
          <p class="text-gray-700 mb-3">
            Send us an email and we'll respond within 24 hours (4 hours for premium customers).
          </p>
          <a href="mailto:support@billing.com" class="inline-flex items-center text-primary-600 hover:underline font-medium">
            support@billing.com
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Live Chat</h3>
              <p class="text-sm text-gray-600">Chat with our team</p>
            </div>
          </div>
          <p class="text-gray-700 mb-3">
            Available Monday-Friday, 9 AM - 6 PM EST. Get instant answers to your questions.
          </p>
          <button class="inline-flex items-center text-success-600 hover:underline font-medium">
            Start Chat
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </button>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Community Forum</h3>
              <p class="text-sm text-gray-600">Ask the community</p>
            </div>
          </div>
          <p class="text-gray-700 mb-3">
            Join our developer community. Share knowledge and get help from other developers.
          </p>
          <a href="https://community.billing.com" target="_blank" class="inline-flex items-center text-purple-600 hover:underline font-medium">
            Visit Forum
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Twitter / X</h3>
              <p class="text-sm text-gray-600">Follow for updates</p>
            </div>
          </div>
          <p class="text-gray-700 mb-3">
            Follow us for the latest updates, tips, and announcements.
          </p>
          <a href="https://twitter.com/billing" target="_blank" class="inline-flex items-center text-blue-600 hover:underline font-medium">
            @billing
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Support Hours -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6" id="hours">Support Hours</h2>
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="font-semibold text-gray-900 mb-3">Standard Support</h3>
            <ul class="space-y-2 text-gray-700">
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Email: 24/7 (24-hour response)
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Chat: Mon-Fri, 9 AM - 6 PM EST
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Community: 24/7
              </li>
            </ul>
          </div>
          
          <div>
            <h3 class="font-semibold text-gray-900 mb-3">Premium Support</h3>
            <ul class="space-y-2 text-gray-700">
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Email: 24/7 (4-hour response)
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Phone: Mon-Fri, 8 AM - 8 PM EST
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Dedicated account manager
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!-- FAQ -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6" id="faq">Frequently Asked Questions</h2>
      
      <div class="space-y-4">
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>How do I get API keys?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            Sign up for an account and navigate to the API Keys section in your dashboard. You'll see both test and live API keys. Never share your live key publicly.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>What payment methods do you support?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            We support credit/debit cards (Visa, Mastercard, Amex), bank transfers (ACH, SEPA), and mobile money (M-Pesa, mobile wallets) in supported regions.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>How do webhooks work?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            Webhooks notify your application when events occur. Configure a webhook URL in your dashboard and we'll send POST requests with event data. Always verify the signature for security.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>Is there a rate limit?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            Yes, the default limit is 100 requests per minute. If you exceed this, you'll receive a 429 error. Premium accounts have higher limits. Contact us if you need an increase.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>Can I test without real payments?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            Absolutely! Use your test API key with test card number 4242424242424242. No real money is charged in test mode.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>How secure is my data?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            We're PCI DSS Level 1 compliant. All data is encrypted in transit (TLS 1.2+) and at rest. We never store raw card numbers - only tokens.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>What if I need help migrating from another platform?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            We offer migration assistance! Contact our support team and we'll help you plan and execute your migration with minimal downtime.
          </p>
        </details>
        
        <details class="bg-white border border-gray-200 rounded-lg p-6 cursor-pointer group">
          <summary class="font-semibold text-gray-900 list-none flex items-center justify-between">
            <span>Do you have SDKs for my programming language?</span>
            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          <p class="text-gray-700 mt-4">
            We offer official SDKs for PHP, JavaScript/Node.js, Python, Ruby, and Go. Check our <NuxtLink to="/docs/sdks" class="text-primary-600 hover:underline">SDKs page</NuxtLink> for installation instructions.
          </p>
        </details>
      </div>
    </div>
    
    <!-- Additional Resources -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6" id="resources">Additional Resources</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="https://status.billing.com" target="_blank" class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-3">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <h3 class="font-semibold text-gray-900">API Status</h3>
          </div>
          <p class="text-sm text-gray-600">
            Check real-time API uptime and performance
          </p>
        </a>
        
        <NuxtLink to="/docs/changelog" class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-3">
            <svg class="w-5 h-5 text-primary-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="font-semibold text-gray-900">Changelog</h3>
          </div>
          <p class="text-sm text-gray-600">
            View latest API updates and releases
          </p>
        </NuxtLink>
        
        <a href="/contact" class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-3">
            <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <h3 class="font-semibold text-gray-900">Enterprise Sales</h3>
          </div>
          <p class="text-sm text-gray-600">
            Talk to sales about custom solutions
          </p>
        </a>
      </div>
    </div>
    
    <!-- Contact Form -->
    <div class="bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Still Need Help?</h2>
      <p class="text-gray-600 mb-6">Send us a message and we'll get back to you shortly</p>
      
      <form class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input
              type="text"
              placeholder="John Doe"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              type="email"
              placeholder="john@example.com"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
          <input
            type="text"
            placeholder="How can we help?"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
          <textarea
            rows="4"
            placeholder="Describe your issue or question..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
          ></textarea>
        </div>
        
        <button
          type="submit"
          class="w-full md:w-auto px-8 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
        >
          Send Message
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'docs'
})

useHead({
  title: 'Support'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'support', text: 'Support', level: 1 },
      { id: 'contact', text: 'Contact Options', level: 2 },
      { id: 'hours', text: 'Support Hours', level: 2 },
      { id: 'faq', text: 'FAQ', level: 2 },
      { id: 'resources', text: 'Additional Resources', level: 2 },
    ])
  }
})
</script>
