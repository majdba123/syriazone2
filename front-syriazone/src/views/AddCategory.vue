<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Form Section -->
      <div class="category-form">
        <h2>Manage Categories</h2>
        <form @submit.prevent="addCategory">
          <div class="form-row">
            <div class="form-group">
              <label for="title">Title:</label>
              <input
                id="title"
                v-model="title"
                type="text"
                required
                class="custom-input"
              />
            </div>
            <div class="form-group">
              <label for="percent">Percent:</label>
              <input
                id="percent"
                v-model="percent"
                type="number"
                required
                class="custom-input"
              />
            </div>
          </div>
          <button type="submit" class="add-button">
            <span class="icon">+</span> Add Category
          </button>
        </form>
      </div>

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
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(category, index) in categories" :key="category.id">
                <td>{{ index + 1 }}</td>

                <td v-if="!category.editing">{{ category.title }}</td>
                <td v-else>
                  <input
                    v-model="category.editTitle"
                    type="text"
                    class="custom-input"
                  />
                </td>

                <td v-if="!category.editing">{{ category.percent }}</td>
                <td v-else>
                  <input
                    v-model="category.editPercent"
                    type="number"
                    class="custom-input"
                  />
                </td>

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

                <td>
                  <div class="action-buttons">
                    <template v-if="!category.editing">
                      <button class="edit-btn" @click="enableEditing(category)">
                        ✏️
                      </button>
                      <button
                        class="delete-btn"
                        @click="deleteCategory(category.id)"
                      >
                        🗑️
                      </button>
                    </template>
                    <template v-else>
                      <button class="save-btn" @click="saveChanges(category)">
                        ✔️
                      </button>
                      <button
                        class="cancel-btn"
                        @click="cancelEditing(category)"
                      >
                        ✖️
                      </button>
                    </template>
                  </div>
                </td>
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
import SideBar from '@/components/SideBar.vue'
import { getData, postData, putData, deleteData } from '@/api'
import { useToast } from 'vue-toastification'

export default {
  name: 'AddCategory',
  components: {
    SideBar,
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
        const response = await getData('/admin/categories/get_all', headers)
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

    async addCategory() {
      const token = window.localStorage.getItem('access_token')
      const headers = {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      }

      try {
        await postData(
          '/admin/categories/store',
          {
            title: this.title,
            percent: this.percent,
          },
          headers
        )

        this.toast.success('Category added successfully')
        this.title = ''
        this.percent = ''
        await this.fetchCategories()
      } catch (error) {
        this.toast.error('Failed to add category')
        console.error('Error adding category:', error)
      }
    },

    enableEditing(category) {
      category.editing = true
    },

    cancelEditing(category) {
      category.editing = false
      category.editTitle = category.title
      category.editPercent = category.percent
    },

    async saveChanges(category) {
      const token = window.localStorage.getItem('access_token')
      const headers = {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      }

      try {
        await putData(
          `/admin/categories/update/${category.id}`,
          {
            title: category.editTitle,
            percent: category.editPercent,
          },
          headers
        )

        this.toast.success('Category updated successfully')
        category.title = category.editTitle
        category.percent = category.editPercent
        category.editing = false
      } catch (error) {
        this.toast.error('Failed to update category')
        console.error('Error updating category:', error)
      }
    },

    async deleteCategory(id) {
      if (!confirm('Are you sure you want to delete this category?')) return

      const token = window.localStorage.getItem('access_token')
      const headers = { Authorization: `Bearer ${token}` }

      try {
        await deleteData(`/admin/categories/delete/${id}`, headers)
        this.toast.success('Category deleted successfully')
        await this.fetchCategories()
      } catch (error) {
        this.toast.error('Failed to delete category')
        console.error('Error deleting category:', error)
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
