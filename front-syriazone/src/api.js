import axios from "axios";
import { useToast } from "vue-toastification";

const API_BASE_URL = "http://127.0.0.1:8000";

// إنشاء نسخة axios مخصصة
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
});

// دالة لمعالجة الأخطاء بشكل مركزي
const handleError = (error, toast) => {
  let errorMessage = "An error occurred";

  if (error.response) {
    // الخطأ من الخادم مع كود حالة
    errorMessage = error.response.data.message || error.response.statusText;
  } else if (error.request) {
    // تم إجراء الطلب ولكن لم يتم استلام أي رد
    errorMessage = "No response received from server";
  } else {
    // حدث خطأ أثناء إعداد الطلب
    errorMessage = error.message;
  }

  toast.error(errorMessage);
  console.error("Error details:", error);
  throw error;
};

// (GET) - جلب البيانات
export const getData = async (endpoint, headers = {}) => {
  const toast = useToast();
  try {
    const response = await apiClient.get(endpoint, { headers });
    toast.success("Data fetched successfully!");
    return response.data;
  } catch (error) {
    handleError(error, toast);
  }
};
// api.js
export const getData2 = async (endpoint, config = {}) => {
  const toast = useToast();
  try {
    const response = await apiClient.get(endpoint, {
      headers: config.headers || {},
      params: config.params || {},
    });
    return response.data;
  } catch (error) {
    handleError(error, toast);
  }
};

// (POST) - إرسال بيانات جديدة
export const postData = async (endpoint, data, headers = {}) => {
  const toast = useToast();
  try {
    const response = await apiClient.post(endpoint, data, { headers });
    toast.success(response.data.message || "Operation completed successfully!");
    return response.data;
  } catch (error) {
    handleError(error, toast);
  }
};

// (PUT) - تحديث البيانات
export const putData = async (endpoint, data, headers = {}) => {
  const toast = useToast();
  try {
    const response = await apiClient.put(endpoint, data, { headers });
    toast.success(response.data.message || "Update successful!");
    return response.data;
  } catch (error) {
    handleError(error, toast);
  }
};

// (DELETE) - حذف البيانات
export const deleteData = async (endpoint, headers = {}) => {
  const toast = useToast();
  try {
    const response = await apiClient.delete(endpoint, { headers });
    toast.success(response.data.message || "Deleted successfully!");
    return response.data;
  } catch (error) {
    handleError(error, toast);
  }
};

// تصدير الدوال وثوابت API
export default {
  API_BASE_URL,
  getData,
  postData,
  putData,
  deleteData,
};
