<template>
  <div :class="cardClasses">
    <div v-if="$slots.header || title" class="border-b border-gray-200 pb-4 mb-4">
      <slot name="header">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
        <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
      </slot>
    </div>
    
    <div :class="bodyClasses">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="border-t border-gray-200 pt-4 mt-4">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  hover: {
    type: Boolean,
    default: false
  },
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'sm', 'normal', 'lg'].includes(value)
  },
  shadow: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'xl'].includes(value)
  }
})

const cardClasses = computed(() => {
  const classes = ['card', 'bg-white', 'rounded-lg']
  
  // Shadow
  if (props.shadow !== 'none') {
    classes.push(`shadow-${props.shadow}`)
  }
  
  // Hover effect
  if (props.hover) {
    classes.push('card-hover')
  }
  
  // Padding
  const paddingClasses = {
    none: 'p-0',
    sm: 'p-4',
    normal: 'p-6',
    lg: 'p-8'
  }
  classes.push(paddingClasses[props.padding])
  
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  return ''
})
</script>
