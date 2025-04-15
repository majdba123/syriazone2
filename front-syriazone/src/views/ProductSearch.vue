<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Search Form -->
      <div class="search-form">
        <h2>Product Search</h2>
        <form @submit.prevent="searchProducts">
          <div class="form-row">
            <div class="form-group">
              <label>Product Name:</label>
              <input v-model="searchParams.name" class="custom-input" />
            </div>

            <div class="form-group">
              <label>Price Range:</label>
              <div class="price-range-group">
                <input
                  v-model="searchParams.min_price"
                  type="number"
                  class="custom-input"
                  placeholder="Min"
                />
                <span class="range-separator">-</span>
                <input
                  v-model="searchParams.max_price"
                  type="number"
                  class="custom-input"
                  placeholder="Max"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Items per Page:</label>
              <select v-model="searchParams.per_page" class="custom-select">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
              </select>
            </div>

            <div class="form-group">
              <label>Category:</label>
              <select
                v-model="selectedCategory"
                class="custom-select"
                @change="fetchSubcategories(selectedCategory)"
              >
                <option :value="null">All Categories</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.title }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Subcategory:</label>
              <select
                v-model="selectedSubcategory"
                class="custom-select"
                :disabled="!selectedCategory"
              >
                <option :value="null">All Subcategories</option>
                <option
                  v-for="sub in subcategories"
                  :key="sub.id"
                  :value="sub.id"
                >
                  {{ sub.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="search-button">
              <i class="fas fa-search"></i> Search
            </button>
            <button type="button" @click="resetSearch" class="reset-button">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Results -->
      <div class="results-section">
        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">{{ error }}</div>

        <template v-else>
          <div v-if="products.length" class="product-grid">
            <div
              v-for="product in products"
              :key="product.id"
              class="product-card"
            >
              <div class="product-image">
                <img
                  v-if="product.images.length"
                  :src="product.images[0]"
                  alt="Product image"
                />
                <div v-else class="no-image">No Image</div>
              </div>
              <div class="product-details">
                <h3>{{ product.name }}</h3>
                <p class="price">${{ product.price }}</p>
                <p class="subcategory">{{ product.subcategory }}</p>
              </div>
            </div>
          </div>
          <div v-else class="no-results">No products found</div>
        </template>

        <!-- Pagination -->
        <div v-if="pagination" class="pagination">
          <button
            v-for="(link, index) in pagination.links"
            :key="index"
            @click="changePage(link.url)"
            :class="{ active: link.active }"
            class="page-btn"
            :disabled="!link.url"
          >
            {{ link.label }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
import { getData2, deleteData } from "@/api";
import { useToast } from "vue-toastification";

export default {
  components: { SideBar },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      searchParams: {
        name: "",
        min_price: "",
        max_price: "",
        per_page: 10,
        page: 1,
      },
      products: [],
      pagination: null,
      loading: false,
      error: null,
      categories: [],
      subcategories: [],
      selectedCategory: null,
      selectedSubcategory: null,
    };
  },
  methods: {
    async getAuthConfig() {
      const token = localStorage.getItem("access_token");
      return {
        headers: { Authorization: `Bearer ${token}` },
        params: this.cleanParams(),
      };
    },

    cleanParams() {
      const params = { ...this.searchParams };

      // إزالة القيم الفارغة
      Object.keys(params).forEach((key) => {
        if (params[key] === "") delete params[key];
      });

      // إدارة معلمات الفلترة الخاصة
      if (this.selectedCategory || this.selectedSubcategory) {
        delete params.page;
        delete params.per_page;
      }

      return params;
    },

    async searchProducts() {
      this.loading = true;
      this.error = null;
      try {
        const config = await this.getAuthConfig();
        let endpoint = "/admin/product/search";

        if (this.selectedSubcategory) {
          endpoint = `/admin/product/subcategory/${this.selectedSubcategory}`;
        } else if (this.selectedCategory) {
          endpoint = `/admin/product/category/${this.selectedCategory}`;
        }

        const response = await getData2(endpoint, config);

        this.products = response.products.data || response.products;
        this.processPagination(response);
      } catch (error) {
        this.error = "Failed to fetch products";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    processPagination(response) {
      if (response.products && response.products.data) {
        this.pagination = {
          current_page: response.products.current_page,
          last_page: response.products.last_page,
          links: response.products.links,
          per_page: response.products.per_page,
        };
      } else {
        this.pagination = null;
      }
    },

    async fetchCategories() {
      try {
        const config = await this.getAuthConfig();
        const response = await getData2("/admin/categories/get_all", config);
        this.categories = response;
        console.log(response);
      } catch (error) {
        console.error("Failed to fetch categories:", error);
      }
    },

    async fetchSubcategories(categoryId) {
      if (!categoryId) {
        this.subcategories = [];
        return;
      }
      try {
        const config = await this.getAuthConfig();
        const response = await getData2(
          `/admin/subcategories/getall?category_id=${categoryId}`,
          config
        );
        this.subcategories = response.data;
        this.selectedSubcategory = null;
      } catch (error) {
        console.error("Failed to fetch subcategories:", error);
      }
    },

    resetSearch() {
      this.searchParams = {
        name: "",
        min_price: "",
        max_price: "",
        per_page: 10,
        page: 1,
      };
      this.selectedCategory = null;
      this.selectedSubcategory = null;
      this.subcategories = [];
      this.searchProducts();
    },
    async deleteProduct(productId) {
      if (confirm("Are you sure you want to delete this product?")) {
        try {
          const config = await this.getAuthConfig();
          await deleteData(`/admin/products/${productId}`, config);
          this.toast.success("Product deleted successfully");
          await this.searchProducts();
        } catch (error) {
          this.toast.error("Failed to delete product");
        }
      }
    },
  },
  mounted() {
    this.fetchCategories();

    this.searchProducts();
  },
};
</script>

<style scoped>
.price-range-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.range-separator {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.search-button,
.reset-button {
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-button {
  background: #3498db;
  color: white;
}

.search-button:hover {
  background: #2980b9;
}

.reset-button {
  background: #95a5a6;
  color: white;
}

.reset-button:hover {
  background: #7f8c8d;
}

.custom-select[disabled] {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.custom-select[disabled] option {
  color: #95a5a6;
}
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

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 1rem;
}

.product-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-5px);
}

.product-image {
  height: 200px;
  background: #f5f6fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
}

.no-image {
  color: #7f8c8d;
  font-size: 1.2rem;
}

.product-details {
  padding: 1.5rem;
}

.price {
  color: #27ae60;
  font-size: 1.4rem;
  font-weight: bold;
  margin: 0.5rem 0;
}

.subcategory {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.product-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.2s;
}
.custom-input,
.custom-select {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}
.search-button {
  background: #3498db;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s, background 0.3s;
}

.custom-input:focus,
.custom-select:focus {
  border-color: #3498db;
  outline: none;
}

.view-btn {
  background: #3498db;
  color: white;
}
.edit-btn {
  background: #f1c40f;
  color: white;
}
.search-form {
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
.delete-btn {
  background: #e74c3c;
  color: white;
}

.pagination {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
  border-radius: 5px;
}

.page-btn.active {
  background: #3498db;
  color: white;
  border-color: #3498db;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
