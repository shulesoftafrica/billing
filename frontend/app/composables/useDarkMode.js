export const useDarkMode = () => {
  const isDark = useState('dark-mode', () => {
    if (process.client) {
      return localStorage.getItem('dark-mode') === 'true' || 
             (!localStorage.getItem('dark-mode') && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }
    return false
  })

  const toggleDark = () => {
    isDark.value = !isDark.value
    if (process.client) {
      localStorage.setItem('dark-mode', isDark.value.toString())
      document.documentElement.classList.toggle('dark', isDark.value)
    }
  }

  // Initialize dark mode on mount
  if (process.client) {
    document.documentElement.classList.toggle('dark', isDark.value)
  }

  return {
    isDark,
    toggleDark
  }
}
