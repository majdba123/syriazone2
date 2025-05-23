import axios from 'axios'
import { useToast } from 'vue-toastification'

const API_BASE_URL = 'http://127.0.0.1:8000/api'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

const handleError = (error, toast) => {
  let errorMessage = 'An error occurred'

  if (error.response) {
    errorMessage = error.response.data.message || error.response.statusText
  } else if (error.request) {
    errorMessage = 'No response received from server'
  } else {
    errorMessage = error.message
  }

  toast.error(errorMessage)
  console.error('Error details:', error)
  throw error
}

export const getData = async (endpoint, headers = {}) => {
  const toast = useToast()
  try {
    const response = await apiClient.get(endpoint, { headers })
    toast.success('Data fetched successfully!')
    return response.data
  } catch (error) {
    handleError(error, toast)
  }
}
// api.js
export const getData2 = async (endpoint, config = {}) => {
  const toast = useToast()
  try {
    const response = await apiClient.get(endpoint, {
      headers: config.headers || {},
      params: config.params || {},
    })
    return response.data
  } catch (error) {
    handleError(error, toast)
  }
}

// (POST)
export const postData = async (endpoint, data, headers = {}) => {
  const toast = useToast()
  try {
    const response = await apiClient.post(endpoint, data, { headers })
    toast.success(response.data.message || 'Operation completed successfully!')
    return response.data
  } catch (error) {
    handleError(error, toast)
  }
}

// (PUT)
export const putData = async (endpoint, data, headers = {}) => {
  const toast = useToast()
  try {
    const response = await apiClient.put(endpoint, data, { headers })
    toast.success(response.data.message || 'Update successful!')
    return response.data
  } catch (error) {
    handleError(error, toast)
  }
}

// (DELETE)
export const deleteData = async (endpoint, headers = {}) => {
  const toast = useToast()
  try {
    const response = await apiClient.delete(endpoint, { headers })
    toast.success(response.data.message || 'Deleted successfully!')
    return response.data
  } catch (error) {
    handleError(error, toast)
  }
}

export default {
  API_BASE_URL,
  getData,
  postData,
  putData,
  deleteData,
}
