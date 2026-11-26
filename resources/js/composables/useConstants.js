import { computed } from 'vue'
import { UFS, POR_PAGINA, ESTADOS } from '../constants'

export function useConstants() {
  const ufs = computed(() => UFS)
  const estados = computed(() => ESTADOS)
  const por_pagina = computed(() => POR_PAGINA)
  const urlSite = computed(
    () =>
      (typeof window !== 'undefined' && window.URL_SITE) ||
      import.meta.env.VITE_URL_SITE ||
      ''
  )

  return { ufs, estados, por_pagina, urlSite }
}

export default useConstants
