<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Filters Section -->
      <div class="vendor-form">
        <h2>Vendor Commission Report</h2>
        <div class="filter-row">
          <div class="filter-group">
            <label>Vendor:</label>
            <select
              v-model="selectedVendor"
              class="custom-select"
              @change="fetchVendorProducts"
            >
              <option value="">All Vendors</option>
              <option
                v-for="vendor in vendors"
                :value="vendor.vendor.id"
                :key="vendor.vendor.id"
              >
                {{ vendor.user.name }}
              </option>
            </select>
          </div>

          <div class="filter-group" v-if="selectedVendor">
            <label>Product:</label>
            <select v-model="selectedProduct" class="custom-select">
              <option value="">All Products</option>
              <option
                v-for="product in vendorProducts"
                :value="product.id"
                :key="product.id"
              >
                {{ product.name }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label>Status:</label>
            <select v-model="selectedStatus" class="custom-select">
              <option value="all">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="complete">Complete</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <div class="filter-row">
          <div class="filter-group">
            <label>From:</label>
            <input type="date" v-model="startDate" class="custom-input" />
          </div>

          <div class="filter-group">
            <label>To:</label>
            <input type="date" v-model="endDate" class="custom-input" />
          </div>

          <button @click="fetchStats" class="filter-button">
            <span class="icon">📊</span> Generate Report
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="summary-cards" v-if="statsData">
        <div class="summary-card">
          <div class="card-icon blue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Total Sales</h3>
            <p>${{ statsData.total_sales || '0.00' }}</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon green">
            <i class="fas fa-percentage"></i>
          </div>
          <div class="card-content">
            <h3>Total Commission</h3>
            <p>${{ statsData.total_commission || '0.00' }}</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon orange">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="card-content">
            <h3>Orders Count</h3>
            <p>{{ statsData.filter.orders_count || '0' }}</p>
          </div>
        </div>
      </div>

      <!-- Detailed Report -->
      <div class="data-table" v-if="statsData">
        <div class="table-header">
          <h3>Commission Details</h3>
          <div class="table-controls">
            <button class="export-button" @click="exportToExcel">
              <span class="icon">📝</span> Export Excel
            </button>
          </div>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <table
          v-if="
            statsData.commission_details &&
            statsData.commission_details.length > 0
          "
        >
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Product</th>
              <th>Category</th>
              <th>Commission Rate</th>
              <th>Amount</th>
              <th>Commission</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(detail, index) in statsData.commission_details"
              :key="index"
            >
              <td>#{{ detail.order_id }}</td>
              <td>{{ detail.product_name }}</td>
              <td>{{ detail.category_name }}</td>
              <td>{{ detail.commission_rate }}%</td>
              <td>${{ detail.order_amount }}</td>
              <td>${{ detail.commission }}</td>
              <td>{{ formatDate(detail.date) }}</td>
              <td>
                <span :class="['status-badge', detail.status]">
                  {{ detail.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="no-data">No commission details found</div>
      </div>

      <!-- Product Specific Stats -->
      <div class="data-table" v-if="productStats">
        <div class="table-header">
          <h3>Product Statistics: {{ productStats.data.product.name }}</h3>
        </div>

        <div class="product-summary">
          <div class="product-info">
            <p>
              <strong>Price:</strong> ${{ productStats.data.product.price }}
            </p>
            <p>
              <strong>Commission Rate:</strong>
              {{ productStats.data.product.commission_rate }}%
            </p>
          </div>

          <table>
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Commission</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="order in productStats.data.orders"
                :key="order.order_product_id"
              >
                <td>#{{ order.order_product_id }}</td>
                <td>{{ formatDate(order.date) }}</td>
                <td>{{ order.quantity }}</td>
                <td>${{ order.amount }}</td>
                <td>${{ order.commission }}</td>
                <td>
                  <span :class="['status-badge', order.status]">
                    {{ order.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from '@/components/SideBar.vue'
import { getData2 } from '@/api'
import { useToast } from 'vue-toastification'

export default {
  name: 'VendorStats',
  components: { SideBar },
  setup() {
    const toast = useToast()
    return { toast }
  },
  data() {
    return {
      vendors: [],
      vendorProducts: [],
      selectedVendor: '',
      selectedProduct: '',
      selectedStatus: 'all',
      startDate: '',
      endDate: '',
      statsData: null,
      productStats: null,
      loading: false,
      error: null,
    }
  },
  methods: {
    async fetchVendors() {
      this.loading = true
      try {
        const token = localStorage.getItem('access_token')
        const response = await getData2('/admin/vendores/get_by_status', {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.vendors = response.data || []
        console.log(this.vendors)
      } catch (error) {
        this.error = 'Failed to load vendors'
        this.toast.error(this.error)
      } finally {
        this.loading = false
      }
    },

    async fetchVendorProducts() {
      if (!this.selectedVendor) return
      this.loading = true
      try {
        const token = localStorage.getItem('access_token')
        const response = await getData2(
          `/admin/product/vendor/${this.selectedVendor}`,

          {
            headers: { Authorization: `Bearer ${token}` },
          }
        )
        console.log(response)
        this.vendorProducts = response.products.data || []
      } catch (error) {
        this.error = 'Failed to load vendor products'
        this.toast.error(this.error)
      } finally {
        this.loading = false
      }
    },

    async fetchStats() {
      this.loading = true
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        // if (this.selectedProduct) {
        //   const params = {
        //     status:
        //       this.selectedStatus !== "all" ? this.selectedStatus : undefined,
        //     vendor_id: this.selectedVendor,
        //   };

        //   const response = await getData2(
        //     `/admin/commissions/calculate/Vendor_Product/${this.selectedProduct}`,
        //     params,
        //     { headers }
        //   );

        //   this.productStats = response;
        //   this.statsData = null;
        // } else {
        const params = {
          status: this.selectedStatus,
          start_date: this.startDate,
          end_date: this.endDate,
        }
        const config = { params, headers }
        console.log(this.selectedVendor)
        const response = await getData2(
          `/admin/commissions/calculate/${this.selectedVendor}`,
          config
        )
        console.log(response)
        this.statsData = response
        this.productStats = null
        // }
      } catch (error) {
        console.log(error)
        this.error = 'Failed to load statistics'
        this.toast.error(this.error)
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      if (!dateString) return ''
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      return new Date(dateString).toLocaleDateString('en-US', options)
    },

    exportToExcel() {
      // Implement Excel export functionality here
      this.toast.info('Export to Excel functionality will be implemented')
    },
  },
  created() {
    this.fetchVendors()
  },
}
</script>

<style scoped>
/* استخدام نفس تنسيقات صفحة البائعين السابقة مع تحسينات */

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

.vendor-form {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  flex: 1;
  min-width: 200px;
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

.filter-button {
  background: #3498db;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  align-self: flex-end;
  transition: background 0.3s;
}

.filter-button:hover {
  background: #2980b9;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.card-icon.blue {
  background: #3498db;
}

.card-icon.green {
  background: #27ae60;
}

.card-icon.orange {
  background: #f39c12;
}

.card-content h3 {
  margin: 0 0 0.5rem;
  font-size: 1rem;
  color: #7f8c8d;
}

.card-content p {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #2c3e50;
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

.export-button {
  background: #27ae60;
  color: white;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.export-button:hover {
  background: #219653;
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

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-badge.pending {
  background: #fef5e7;
  color: #f39c12;
}

.status-badge.complete {
  background: #e8f6f3;
  color: #27ae60;
}

.status-badge.cancelled {
  background: #fdecea;
  color: #e74c3c;
}

.product-summary {
  margin-top: 1.5rem;
}

.product-info {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

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

.no-data {
  padding: 2rem;
  text-align: center;
  color: #7f8c8d;
  background: #f8f9fa;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
  }

  .filter-row {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-group {
    min-width: 100%;
  }

  .filter-button {
    width: 100%;
  }

  .table-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>
