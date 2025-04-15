<template>
  <div class="container">
    <SideBarvendor />
    <div class="dashboard">
      <!-- Product Form -->
      <div class="product-form">
        <h2>Manage Products</h2>
        <form @submit.prevent="addProduct" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label>Product Name:</label>
              <input v-model="product.name" required class="custom-input" />
            </div>

            <div class="form-group">
              <label>Description:</label>
              <textarea
                v-model="product.description"
                class="custom-input"
              ></textarea>
            </div>

            <div class="form-group">
              <label>Price:</label>
              <input
                v-model="product.price"
                type="number"
                step="0.01"
                required
                class="custom-input"
              />
            </div>

            <div class="form-group">
              <label>Subcategory:</label>
              <select
                v-model="product.sub_category_id"
                required
                class="custom-select"
              >
                <option value="">Select Subcategory</option>
                <option
                  v-for="sub in subcategories"
                  :value="sub.id"
                  :key="sub.id"
                >
                  {{ sub.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Images:</label>
              <input
                type="file"
                multiple
                @change="handleFileUpload"
                accept="image/*"
                class="custom-input"
              />
            </div>
          </div>
          <button type="submit" class="add-button">Add Product</button>
        </form>
      </div>

      <!-- Filters -->
      <div class="filters-section">
        <h3>Filters</h3>
        <div class="filter-row">
          <input
            v-model="filters.name"
            placeholder="Search by name"
            class="custom-input"
          />

          <select v-model="filters.subcategory_id" class="custom-select">
            <option value="">All Subcategories</option>
            <option v-for="sub in subcategories" :value="sub.id" :key="sub.id">
              {{ sub.name }}
            </option>
          </select>

          <input
            v-model="filters.min_price"
            type="number"
            placeholder="Min price"
            class="custom-input"
          />
          <input
            v-model="filters.max_price"
            type="number"
            placeholder="Max price"
            class="custom-input"
          />

          <button @click="applyFilters" class="filter-button">
            Apply Filters
          </button>
          <button @click="resetFilters" class="filter-button">Reset</button>
        </div>
      </div>

      <!-- Products Table -->
      <div class="data-table">
        <div class="table-header">
          <h3>Products List</h3>
          <button class="refresh-button" @click="fetchProducts">
            ↻ Refresh
          </button>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="products.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Subcategory</th>
                <th>Images</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(product, index) in products" :key="product.id">
                <td>{{ index + 1 }}</td>

                <!-- Editing Mode -->
                <template v-if="product.editing">
                  <td>
                    <input v-model="product.editName" class="custom-input" />
                  </td>
                  <td>
                    <textarea
                      v-model="product.editDescription"
                      class="custom-input"
                    ></textarea>
                  </td>
                  <td>
                    <input
                      v-model="product.editPrice"
                      type="number"
                      step="0.01"
                      class="custom-input"
                    />
                  </td>
                  <td>
                    <select
                      v-model="product.editSubcategory"
                      class="custom-select"
                    >
                      <option
                        v-for="sub in subcategories"
                        :value="sub.id"
                        :key="sub.id"
                      >
                        {{ sub.name }}
                      </option>
                    </select>
                  </td>
                  <td>
                    <input
                      type="file"
                      multiple
                      @change="handleEditFileUpload"
                      accept="image/*"
                    />
                    <div class="image-preview">
                      <div
                        v-for="(img, idx) in product.editImages"
                        :key="idx"
                        class="image-item"
                      >
                        <img :src="img.url" class="thumbnail" />
                        <button
                          @click="removeImage(idx)"
                          class="remove-image-btn"
                        >
                          ×
                        </button>
                      </div>
                    </div>
                  </td>
                </template>

                <!-- View Mode -->
                <template v-else>
                  <td>{{ product.name }}</td>
                  <td>{{ product.description }}</td>
                  <td>${{ product.price.toFixed(2) }}</td>
                  <td>{{ getSubcategoryName(product.sub_category_id) }}</td>
                  <td>
                    <div class="image-preview">
                      <img
                        v-for="(img, idx) in product.images"
                        :key="idx"
                        :src="img.url"
                        class="thumbnail"
                      />
                    </div>
                  </td>
                </template>

                <td>
                  <div class="action-buttons">
                    <template v-if="!product.editing">
                      <button class="edit-btn" @click="startEditing(product)">
                        ✏️
                      </button>
                      <button
                        class="delete-btn"
                        @click="deleteProduct(product.id)"
                      >
                        🗑️
                      </button>
                    </template>
                    <template v-else>
                      <button class="save-btn" @click="saveProduct(product)">
                        ✔️
                      </button>
                      <button
                        class="cancel-btn"
                        @click="cancelEditing(product)"
                      >
                        ✖️
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ No products found</div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { getData, postData, putData, deleteData } from "@/api";
import { useToast } from "vue-toastification";
import SideBarvendor from "@/components/SideBarvendor.vue";

export default {
  name: "AddProduct",
  components: { SideBarvendor },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      product: {
        name: "",
        description: "",
        price: 0,
        sub_category_id: "",
        images: [],
      },
      filters: {
        name: "",
        subcategory_id: "",
        min_price: "",
        max_price: "",
      },
      products: [],
      subcategories: [],
      loading: false,
      error: null,
      editFiles: [],
    };
  },
  computed: {
    getSubcategoryName() {
      return (id) => {
        const sub = this.subcategories.find((s) => s.id === id);
        return sub ? sub.name : "Unknown";
      };
    },
  },
  methods: {
    async fetchSubcategories() {
      const token = localStorage.getItem("access_token");
      const headers = { Authorization: `Bearer ${token}` };
      try {
        const response = await getData("/vendor/subcategories/getall", headers);
        this.subcategories = response;
      } catch (error) {
        this.toast.error("Failed to load subcategories");
      }
    },

    async fetchProducts() {
      this.loading = true;
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const params = new URLSearchParams();
        if (this.filters.name) params.append("name", this.filters.name);
        if (this.filters.subcategory_id)
          params.append("subcategory_id", this.filters.subcategory_id);
        if (this.filters.min_price)
          params.append("min_price", this.filters.min_price);
        if (this.filters.max_price)
          params.append("max_price", this.filters.max_price);

        const response = await getData(
          `/vendor/product/get_all?${params.toString()}`,
          headers
        );
        this.products = response.map((p) => ({
          ...p,
          editing: false,
          editName: p.name,
          editDescription: p.description,
          editPrice: p.price,
          editSubcategory: p.sub_category_id,
          editImages: [...p.images],
        }));
      } catch (error) {
        this.error = "Failed to load products";
      } finally {
        this.loading = false;
      }
    },

    handleFileUpload(e) {
      this.product.images = [...e.target.files];
    },

    handleEditFileUpload(e) {
      this.editFiles = [...e.target.files];
    },

    async addProduct() {
      const token = localStorage.getItem("access_token");
      const headers = { Authorization: `Bearer ${token}` };
      const formData = new FormData();
      formData.append("name", this.product.name);
      formData.append("description", this.product.description);
      formData.append("price", this.product.price);
      formData.append("sub_category_id", this.product.sub_category_id);

      this.product.images.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });

      try {
        await postData("/vendor/product/store", formData, headers);
        this.toast.success("Product added successfully");
        this.resetForm();
        await this.fetchProducts();
      } catch (error) {
        this.toast.error("Failed to add product");
      }
    },

    startEditing(product) {
      product.editing = true;
      this.editFiles = [];
    },

    async saveProduct(product) {
      const token = localStorage.getItem("access_token");
      const headers = { Authorization: `Bearer ${token}` };
      const formData = new FormData();
      formData.append("name", product.editName);
      formData.append("description", product.editDescription);
      formData.append("price", product.editPrice);
      formData.append("sub_category_id", product.editSubcategory);

      // Add new files
      this.editFiles.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });

      try {
        await putData(
          `/vendor/product/update/${product.id}`,
          formData,
          headers
        );
        this.toast.success("Product updated successfully");
        await this.fetchProducts();
      } catch (error) {
        this.toast.error("Failed to update product");
      }
    },

    async deleteProduct(id) {
      const token = localStorage.getItem("access_token");
      const headers = { Authorization: `Bearer ${token}` };
      if (!confirm("Are you sure you want to delete this product?")) return;

      try {
        await deleteData(`/product_provider/product/delete/${id}`, headers);
        this.toast.success("Product deleted successfully");
        await this.fetchProducts();
      } catch (error) {
        this.toast.error("Failed to delete product");
      }
    },

    removeImage(index) {
      this.product.editImages.splice(index, 1);
    },

    applyFilters() {
      this.fetchProducts();
    },

    resetFilters() {
      this.filters = {
        name: "",
        subcategory_id: "",
        min_price: "",
        max_price: "",
      };
      this.fetchProducts();
    },

    resetForm() {
      this.product = {
        name: "",
        description: "",
        price: 0,
        sub_category_id: "",
        images: [],
      };
    },
  },
  created() {
    this.fetchSubcategories();
    this.fetchProducts();
  },
};
</script>

<style scoped>
/* General Styles */
.container {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard {
  padding: 2rem;
  background: #f8f9fa;
}

/* Form Styles */
.product-form,
.filters-section,
.data-table {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  border: 1px solid #eaeaea;
}

h2,
h3 {
  color: #2c3e50;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

h2 {
  font-size: 1.8rem;
  border-bottom: 2px solid #3498db;
  padding-bottom: 0.5rem;
  display: inline-block;
}

/* Input Fields */
.custom-input,
.custom-select,
textarea {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background-color: #f8f9fa;
}

.custom-input:focus,
.custom-select:focus,
textarea:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
  background-color: white;
}

textarea {
  resize: vertical;
  min-height: 100px;
}

/* Buttons */
.add-button,
.refresh-button,
.filter-button {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.add-button {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  box-shadow: 0 2px 5px rgba(41, 128, 185, 0.3);
}

.add-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(41, 128, 185, 0.4);
}

.refresh-button {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
  box-shadow: 0 2px 5px rgba(46, 204, 113, 0.3);
}

.refresh-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(46, 204, 113, 0.4);
}

.filter-button {
  background: linear-gradient(135deg, #9b59b6, #8e44ad);
  color: white;
  box-shadow: 0 2px 5px rgba(155, 89, 182, 0.3);
}

.filter-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(155, 89, 182, 0.4);
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
  transition: all 0.3s ease;
  font-size: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.edit-btn {
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
}

.delete-btn {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
}

.save-btn {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
}

.cancel-btn {
  background: linear-gradient(135deg, #95a5a6, #7f8c8d);
  color: white;
}

.edit-btn:hover,
.delete-btn:hover,
.save-btn:hover,
.cancel-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

/* Table Styles */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
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
  color: #2c3e50;
  position: sticky;
  top: 0;
}

tr:hover {
  background-color: #f5f7fa;
}

/* Image Preview */
.image-preview {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.thumbnail {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ddd;
  transition: transform 0.3s;
}

.thumbnail:hover {
  transform: scale(1.1);
}

.image-item {
  position: relative;
  margin: 0.3rem;
}

.remove-image-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  cursor: pointer;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: all 0.2s;
}

.remove-image-btn:hover {
  transform: scale(1.2);
  background: #c0392b;
}

/* Form Layout */
.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

/* Status Indicators */
.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  display: inline-block;
}

.status-pending {
  background: #fef5e7;
  color: #f39c12;
}

.status-active {
  background: #e8f6f3;
  color: #27ae60;
}

/* Loading and Messages */
.loading-overlay {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(52, 152, 219, 0.2);
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
  border-left: 4px solid #e74c3c;
}

.no-data {
  color: #7f8c8d;
  padding: 2rem;
  text-align: center;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px dashed #ddd;
}

/* File Input Styling */
input[type="file"] {
  width: 100%;
  padding: 0.5rem;
  border: 1px dashed #3498db;
  border-radius: 8px;
  background-color: rgba(52, 152, 219, 0.05);
  transition: all 0.3s;
}

input[type="file"]:hover {
  background-color: rgba(52, 152, 219, 0.1);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
  }

  .form-row,
  .filter-row {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-wrap: wrap;
  }
}
</style>
