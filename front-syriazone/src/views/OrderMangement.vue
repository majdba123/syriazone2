<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Filters Section -->
      <div class="search-form">
        <h2>Orders Management</h2>
        <form @submit.prevent="applyFilters">
          <div class="form-row">
            <div class="form-group">
              <label>Filter Type:</label>
              <select v-model="currentFilter" class="custom-select">
                <option value="status">By Status</option>
                <option value="vendor">By Vendor</option>
                <option value="subcategory">By Subcategory</option>
                <option value="price">By Price</option>
                <option value="category">By Category</option>
              </select>
            </div>

            <div class="form-group" v-if="currentFilter === 'status'">
              <label>Status:</label>
              <select v-model="selectedStatus" class="custom-select">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
                <option value="complete">Complete</option>
              </select>
            </div>
            <div class="form-group" v-if="currentFilter === 'price'">
              <label>Price Range:</label>
              <div class="price-range-group">
                <input
                  v-model="minPrice"
                  type="number"
                  class="custom-input"
                  placeholder="Min"
                />
                <span class="range-separator">-</span>
                <input
                  v-model="maxPrice"
                  type="number"
                  class="custom-input"
                  placeholder="Max"
                />
              </div>
            </div>
            <div class="form-group" v-if="currentFilter === 'category'">
              <label>Category:</label>
              <select v-model="selectedCategory" class="custom-select">
                <option value="">Select Category</option>
                <option
                  v-for="category in categories"
                  :value="category.id"
                  :key="category.id"
                >
                  {{ category.title }}
                </option>
              </select>
            </div>
            <div class="form-group" v-if="currentFilter === 'vendor'">
              <label>Vendor:</label>
              <select v-model="selectedVendor" class="custom-select">
                <option value="">Select Vendor</option>
                <option
                  v-for="vendor in vendors"
                  :value="vendor.vendor.id"
                  :key="vendor.vendor.id"
                >
                  {{ vendor.user.name }}
                </option>
              </select>
            </div>

            <div class="form-group" v-if="currentFilter === 'subcategory'">
              <label>Subcategory:</label>
              <select v-model="selectedSubcategory" class="custom-select">
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
              <label>Items per Page:</label>
              <select v-model="pagination.per_page" class="custom-select">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="search-button">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
            <button type="button" @click="resetFilters" class="reset-button">
              <i class="fas fa-undo"></i> Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Results Section -->
      <div class="results-section">
        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">{{ error }}</div>

        <template v-else>
          <div class="data-table">
            <div class="table-header">
              <h3>Orders List</h3>
              <div class="pagination">
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

            <table v-if="orders.length > 0">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>User</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Products</th>
                  <th>Vendor</th>
                  <th>Date</th>
                  <!-- <th>Actions</th> -->
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>
                    <div v-if="order.user" class="user-info">
                      <div class="user-name">{{ order.user.name }}</div>
                      <div class="user-email">{{ order.user.email }}</div>
                    </div>
                    <span v-else>-</span>
                  </td>
                  <td>${{ order.total_price }}</td>
                  <td>
                    <span :class="['status-badge', order.status]">
                      {{ order.status }}
                    </span>
                  </td>
                  <td>
                    <div class="products-list">
                      <div
                        v-for="item in order.order_product"
                        :key="item.id"
                        class="product-item"
                      >
                        {{ item.product.name }} (x{{ item.quantity }})
                      </div>
                    </div>
                  </td>
                  <td>
                    <span v-if="order.vendor" class="vendor-info">
                      {{ order.vendor.name }}
                    </span>
                    <span v-else>-</span>
                  </td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <!-- <td>
                    <div class="action-buttons">
                      <button
                        class="view-btn action-btn"
                        @click="viewOrderDetails(order)"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button
                        class="edit-btn action-btn"
                        @click="updateOrderStatus(order)"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                    </div>
                  </td> -->
                </tr>
              </tbody>
            </table>
            <div v-else class="no-results">No orders found</div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
import { getData2 } from "@/api";
import { useToast } from "vue-toastification";

export default {
  name: "OrderManagement",
  components: { SideBar },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      categories: [],
      minPrice: null,
      maxPrice: null,
      selectedCategory: null,
      orders: [],
      vendors: [],
      subcategories: [],
      currentFilter: "status",
      selectedStatus: "all",
      selectedVendor: "",
      selectedSubcategory: "",
      loading: false,
      error: null,
      pagination: {
        per_page: 10,
        links: [],
      },
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
      const params = {
        min_price: this.minPrice,
        max_price: this.maxPrice,
        category_id: this.selectedCategory,
        per_page: this.pagination.per_page,
        status: this.selectedStatus !== "all" ? this.selectedStatus : null,
        vendor_id: this.selectedVendor,
        subcategory_id: this.selectedSubcategory,
      };

      // Remove null values
      Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === "") delete params[key];
      });

      return params;
    },

    async fetchData() {
      this.loading = true;
      this.error = null;

      try {
        const config = await this.getAuthConfig();
        let endpoint = `/admin/orders/get_all_by_status?status=${this.selectedStatus}`;

        if (this.currentFilter === "vendor") {
          endpoint = `/admin/orders/get_all/ByVendor/${this.selectedVendor}`;
        } else if (this.currentFilter === "subcategory") {
          endpoint = `/admin/orders/get_all_by_sub_category/${this.selectedSubcategory}`;
        } else if (this.currentFilter === "price") {
          endpoint = `/admin/orders/get_all_by_price?min_price=${this.minPrice}&max_price=${this.maxPrice}`;
        } else if (this.currentFilter === "category") {
          endpoint = `/admin/orders/get_all_by_category/${this.selectedCategory}`;
        }

        const response = await getData2(endpoint, config);

        this.orders = response.orders.data || response.orders;
        this.processPagination(response);
      } catch (error) {
        this.error = "Failed to load orders";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async fetchCategories() {
      try {
        const config = await this.getAuthConfig();
        const response = await getData2("/admin/categories/get_all", config);
        this.categories = response.data || response;
      } catch (error) {
        this.toast.error("Failed to load categories");
      }
    },

    processPagination(response) {
      if (response.orders && response.orders.links) {
        this.pagination.links = response.orders.links;
      } else {
        this.pagination.links = [];
      }
    },

    async fetchVendors() {
      try {
        const config = await this.getAuthConfig();
        const response = await getData2(
          "/admin/vendores/get_by_status",
          config
        );
        this.vendors = response.data || response;
      } catch (error) {
        this.toast.error("Failed to load vendors");
      }
    },

    async fetchSubcategories() {
      try {
        const config = await this.getAuthConfig();
        const response = await getData2("/admin/subcategories/getall", config);
        this.subcategories = response.data || response;
      } catch (error) {
        this.toast.error("Failed to load subcategories");
      }
    },

    applyFilters() {
      this.pagination.links = [];
      this.fetchData();
    },

    resetFilters() {
      this.minPrice = null;
      this.maxPrice = null;
      this.selectedCategory = null;
      this.currentFilter = "status";
      this.selectedStatus = "all";
      this.selectedVendor = "";
      this.selectedSubcategory = "";
      this.pagination.per_page = 10;
      this.fetchData();
    },

    changePage(url) {
      if (!url) return;
      const page = new URL(url).searchParams.get("page");
      this.pagination.current_page = page;
      this.fetchData();
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    viewOrderDetails(order) {
      // Implement view details logic
      console.log("View order:", order);
    },

    updateOrderStatus(order) {
      // Implement update status logic
      console.log("Update status for order:", order);
    },
  },
  mounted() {
    this.selectedStatus = "all";
    this.fetchCategories();
    this.fetchVendors();
    this.fetchSubcategories();
    this.fetchData();
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
  font-size: 1rem;
  padding: 0 0.5rem;
}

.custom-input[type="number"] {
  width: 100px;
}
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
/* نفس الأنماط المستخدمة في صفحة المنتجات */
.user-info {
  line-height: 1.4;
}

.user-name {
  font-weight: 500;
}

.user-email {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.products-list {
  max-height: 150px;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.product-item {
  padding: 0.3rem 0;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  display: inline-block;
}

.status-badge.pending {
  background: #fef5e7;
  color: #f39c12;
}

.status-badge.cancelled {
  background: #fdecea;
  color: #e74c3c;
}
.search-button {
  background: #3498db;
  color: white;
}

.search-button:hover {
  background: #2980b9;
}
.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.status-badge.complete {
  background: #e8f6f3;
  color: #27ae60;
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
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s, background 0.3s;
}
.action-btn {
  width: 35px;
  height: 35px;
  border: none;
  border-radius: 50%;
  margin: 0 2px;
  cursor: pointer;
  transition: transform 0.2s;
}

.action-btn:hover {
  transform: scale(1.1);
}

.view-btn {
  background: #3498db;
  color: white;
}

.edit-btn {
  background: #f1c40f;
  color: white;
}

.vendor-info {
  font-weight: 500;
  color: #2c3e50;
}
</style>
