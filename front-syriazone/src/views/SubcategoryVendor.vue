<template>
  <div class="container">
    <SideBarvendor />
    <div class="dashboard">
      <!-- Table Section -->
      <div class="data-table">
        <div class="table-header">
          <h3>Sub-Categories List</h3>
          <button class="refresh-button" @click="fetchSubCategories">
            <span class="icon">↻</span> Refresh
          </button>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="subCategories.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Main Category</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(sub, index) in subCategories" :key="sub.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <span v-if="!sub.editing">{{ sub.name }}</span>
                </td>
                <td>
                  <span v-if="!sub.editing">
                    {{ getCategoryName(sub.category_id) }}
                  </span>
                </td>
                <td>{{ formatDate(sub.created_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ No data available</div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getData } from "@/api";
import { useToast } from "vue-toastification";
import SideBarvendor from "@/components/SideBarvendor.vue";

export default {
  name: "SubcategoryVendor",
  components: {
    SideBarvendor,
  },
  data() {
    return {
      subCategoryName: "",
      selectedCategory: "",
      categories: [],
      subCategories: [],
      loading: false,
      error: null,
      toast: useToast(),
    };
  },
  computed: {
    getCategoryName() {
      return (categoryId) => {
        const category = this.categories.find((c) => c.id === categoryId);
        return category ? category.title : "Unknown";
      };
    },
  },
  async created() {
    await this.fetchCategories();
    await this.fetchSubCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const response = await getData("/vendor/categories/get_all", headers);
        this.categories = response;
      } catch (error) {
        this.toast.error("Failed to load main categories");
        console.error(error);
      }
    },

    async fetchSubCategories() {
      this.loading = true;
      this.error = null;
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const response = await getData("/vendor/subcategories/getall", headers);
        this.subCategories = response.map((sub) => ({
          ...sub,
          editing: false,
          editName: sub.name,
          editCategoryId: sub.category_id,
        }));
      } catch (error) {
        this.error = "Error loading data";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    async FetchSubCategories() {
      this.loading = true;
      this.error = null;
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const response = await getData("/vendor/subcategories/getall", headers);

        this.subCategories = response.map((sub) => ({
          ...sub,
          editing: false,
          editName: sub.name,
          editCategoryId: String(sub.category_id), // تحويل إلى string
        }));
      } catch (error) {
        this.error = "حدث خطأ أثناء جلب البيانات";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    formatDate(dateString) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(dateString).toLocaleDateString("en-US", options);
    },
  },
};
</script>

<style scoped>
/* Basic Styles */
.container {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
}

.dashboard {
  padding: 2rem;
  background: #f8f9fa;
}

/* Form Styling */
.category-form {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.custom-select,
.custom-input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.custom-select:focus,
.custom-input:focus {
  border-color: #3498db;
  outline: none;
}

.add-button {
  background: #3498db;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s, background 0.3s;
}

.add-button:hover {
  background: #2980b9;
  transform: translateY(-1px);
}

/* Table Styling */
.data-table {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.refresh-button {
  background: #27ae60;
  color: white;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: 600;
}

tr:hover {
  background: #f8f9fa;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn,
.save-btn,
.cancel-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s;
}

.edit-btn {
  background: #3498db;
  color: white;
}
.delete-btn {
  background: #e74c3c;
  color: white;
}
.save-btn {
  background: #27ae60;
  color: white;
}
.cancel-btn {
  background: #95a5a6;
  color: white;
}

button:hover {
  transform: scale(1.1);
}

/* Loading and Error States */
.loading-overlay {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.error-message {
  color: #e74c3c;
  padding: 1rem;
  background: #fdecea;
  border-radius: 8px;
  text-align: center;
}

.no-data {
  color: #7f8c8d;
  padding: 2rem;
  text-align: center;
  background: #f8f9fa;
  border-radius: 8px;
}

.icon {
  margin-right: 0.5rem;
}
</style>
