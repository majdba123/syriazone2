import axios from "axios";
import { useToast } from "vue-toastification";

const API_BASE_URL = "http://127.0.0.1:8000";
export default {
  API_BASE_URL,
};

const apiClient = axios.create({
  baseURL: API_BASE_URL,

});

// (GET)
export const getData = async (endpoint, headers) => {
  const toast = useToast();
  try {
    const response = await apiClient.get(endpoint, { headers });
    toast.success("Data fetched successfully!");
    return response.data;
  } catch (error) {
    toast.error("Error fetching data: " + error.message);
    throw error;
  }
};

// (POST)
export const postData = async (endpoint, data, headers) => {
  const toast = useToast();
  try {
    const response = await apiClient.post(endpoint, data, { headers });
    toast.success(response.data.message);
    return response;
  } catch (error) {
    console.error("Error details:", error);
    toast.error("Error posting data: " + error.message);
    throw error;
  }
};
