import { ref } from 'vue'

export function usePreload(initial = false) {
  const preload = ref(initial)

  const setPreload = (value) => {
    preload.value = !!value
  }

  const withPreload = async (fn) => {
    setPreload(true)
    try {
      return await fn()
    } finally {
      setPreload(false)
    }
  }

  return { preload, setPreload, withPreload }
}

export default usePreload
