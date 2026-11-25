import axios from 'axios'

const baseAdmin = () => (typeof URL_ADMIN !== 'undefined' ? URL_ADMIN : '/g')
const baseSite = () => (typeof URL_SITE !== 'undefined' ? URL_SITE : window.location.origin)

export const api = {
  get(path, config = {}) {
    return axios.get(`${baseAdmin()}${path.startsWith('/') ? path : `/${path}`}`, config)
  },
  post(path, data, config = {}) {
    return axios.post(`${baseAdmin()}${path.startsWith('/') ? path : `/${path}`}`, data, config)
  },
  put(path, data, config = {}) {
    return axios.put(`${baseAdmin()}${path.startsWith('/') ? path : `/${path}`}`, data, config)
  },
  patch(path, data, config = {}) {
    return axios.patch(`${baseAdmin()}${path.startsWith('/') ? path : `/${path}`}`, data, config)
  },
  delete(path, config = {}) {
    return axios.delete(`${baseAdmin()}${path.startsWith('/') ? path : `/${path}`}`, config)
  },
  site: {
    get(path, config = {}) {
      return axios.get(`${baseSite()}${path.startsWith('/') ? path : `/${path}`}`, config)
    },
    post(path, data, config = {}) {
      return axios.post(`${baseSite()}${path.startsWith('/') ? path : `/${path}`}`, data, config)
    },
  },
}

export default api
