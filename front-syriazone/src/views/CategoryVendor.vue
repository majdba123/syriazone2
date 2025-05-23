<template>
  <div class="container">
    <SideBarvendor />
    <div class="dashboard">
      <!-- Table Section -->
      <div class="data-table">
        <div class="table-header">
          <h3>Categories List</h3>
          <button class="refresh-button" @click="fetchCategories">
            <span class="icon">↻</span> Refresh
          </button>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="categories.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Percent</th>
                <th>Sub Categories</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(category, index) in categories" :key="category.id">
                <td>{{ index + 1 }}</td>

                <td v-if="!category.editing">{{ category.title }}</td>

                <td v-if="!category.editing">{{ category.percent }}</td>

                <td>
                  <div class="sub-categories">
                    <span
                      v-for="sub in category.sub_category"
                      :key="sub.id"
                      class="sub-category-badge"
                    >
                      {{ sub.name }}
                    </span>
                    <span v-if="category.sub_category.length === 0">-</span>
                  </div>
                </td>

                <td></td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ No categories found</div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getData } from '@/api'
import { useToast } from 'vue-toastification'
import SideBarvendor from '@/components/SideBarvendor.vue'

export default {
  name: 'CategoryVendor',
  components: {
    SideBarvendor,
  },
  setup() {
    const toast = useToast()
    return { toast }
  },
  data() {
    return {
      title: '',
      percent: '',
      categories: [],
      loading: false,
      error: null,
    }
  },
  async created() {
    await this.fetchCategories()
  },
  methods: {
    async fetchCategories() {
      this.loading = true
      this.error = null

      const token = window.localStorage.getItem('access_token')
      const headers = { Authorization: `Bearer ${token}` }

      try {
        const response = await getData('/vendor/categories/get_all', headers)
        this.categories = response.map((cat) => ({
          ...cat,
          editing: false,
          editTitle: cat.title,
          editPercent: cat.percent,
        }))
      } catch (error) {
        this.error = 'Failed to load categories. Please try again.'
        console.error('Error fetching categories:', error)
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
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

.category-form {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.custom-input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

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

.icon {
  margin-right: 0.5rem;
}

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

.sub-categories {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.sub-category-badge {
  background: #e0f7fa;
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  color: #00838f;
}

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
  background: #2ecc71;
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
</style>
