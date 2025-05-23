<template>
  <div class="container">
    <SideBarvendor />
    <div class="dashboard">
      <!-- Filters Section -->
      <div class="filters-section">
        <h2>إدارة الطلبات</h2>
        <div class="filter-row">
          <select
            v-model="statusFilter"
            class="status-filter"
            @change="fetchOrders"
          >
            <option value="">جميع الحالات</option>
            <option value="pending">قيد الانتظار</option>
            <option value="processing">قيد المعالجة</option>
            <option value="completed">مكتمل</option>
            <option value="cancelled">ملغى</option>
          </select>

          <button class="refresh-button" @click="fetchOrders">
            <span class="icon">↻</span> تحديث
          </button>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="data-table">
        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="filteredOrders.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>رقم الطلب</th>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر الإجمالي</th>
                <th>الحالة</th>
                <th>تاريخ الطلب</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(order, index) in paginatedOrders" :key="order.id">
                <td>{{ index + 1 + (currentPage - 1) * perPage }}</td>
                <td>#{{ order.order_id }}</td>
                <td>
                  <div class="product-info">
                    <span class="product-name">{{ order.product.name }}</span>
                    <div class="product-attributes">
                      <span
                        v-for="(
                          attr, idx
                        ) in order.product.attributes_data.slice(0, 2)"
                        :key="idx"
                        class="attribute"
                      >
                        {{ attr.name_attributes }}: {{ attr.value_attributes }}
                      </span>
                      <span
                        v-if="order.product.attributes_data.length > 2"
                        class="more-attributes"
                      >
                        +{{ order.product.attributes_data.length - 2 }} أكثر
                      </span>
                    </div>
                  </div>
                </td>
                <td>{{ order.quantity }}</td>
                <td>${{ order.total_price }}</td>
                <td>
                  <span :class="['status-badge', order.status]">
                    {{ getStatusText(order.status) }}
                  </span>
                </td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td>
                  <div class="action-buttons">
                    <button
                      class="details-btn"
                      @click="showOrderDetails(order)"
                    >
                      التفاصيل
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ لا توجد طلبات متاحة</div>
        </template>

        <!-- Pagination -->
        <div v-if="filteredOrders.length > 0" class="pagination">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="pagination-button"
          >
            السابق
          </button>
          <span class="page-info">
            الصفحة {{ currentPage }} من {{ totalPages }}
          </span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="pagination-button"
          >
            التالي
          </button>
        </div>
      </div>

      <!-- Order Details Modal -->
      <div
        v-if="selectedOrder"
        class="modal-overlay"
        @click.self="selectedOrder = null"
      >
        <div class="modal-content">
          <button class="close-modal" @click="selectedOrder = null">✖</button>
          <h3>تفاصيل الطلب #{{ selectedOrder.order_id }}</h3>

          <div class="order-details">
            <div class="detail-row">
              <span class="detail-label">تاريخ الطلب:</span>
              <span class="detail-value">{{
                formatDate(selectedOrder.created_at)
              }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">الحالة:</span>
              <span :class="['status-badge', selectedOrder.status]">
                {{ getStatusText(selectedOrder.status) }}
              </span>
            </div>

            <div class="product-details">
              <h4>تفاصيل المنتج</h4>
              <div class="product-card">
                <div class="product-header">
                  <span class="product-name">{{
                    selectedOrder.product.name
                  }}</span>
                  <span class="product-price"
                    >${{ selectedOrder.total_price }}</span
                  >
                </div>
                <div class="product-quantity">
                  الكمية: {{ selectedOrder.quantity }}
                </div>

                <div class="attributes-list">
                  <div
                    v-for="attr in selectedOrder.product.attributes_data"
                    :key="attr.attribute_id"
                    class="attribute-item"
                  >
                    <span class="attribute-name"
                      >{{ attr.name_attributes }}:</span
                    >
                    <span class="attribute-value">{{
                      attr.value_attributes
                    }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="actions">
              <button
                v-if="
                  selectedOrder.status !== 'completed' &&
                  selectedOrder.status !== 'cancelled'
                "
                @click="updateOrderStatus(selectedOrder)"
                class="update-status-btn"
              >
                تحديث الحالة
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getData } from '@/api'
import { useToast } from 'vue-toastification'
import SideBarvendor from '@/components/SideBarvendor.vue'

export default {
  name: 'OrdersPage',
  components: { SideBarvendor },
  setup() {
    const toast = useToast()
    return { toast }
  },
  data() {
    return {
      orders: [],
      statusFilter: '',
      searchQuery: '',
      loading: false,
      error: null,
      selectedOrder: null,
      currentPage: 1,
      perPage: 10,
    }
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders

      // Filter by status
      if (this.statusFilter) {
        filtered = filtered.filter(
          (order) => order.status === this.statusFilter
        )
      }

      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(
          (order) =>
            order.order_id.toString().includes(query) ||
            order.product.name.toLowerCase().includes(query)
        )
      }

      return filtered
    },
    paginatedOrders() {
      const start = (this.currentPage - 1) * this.perPage
      const end = start + this.perPage
      return this.filteredOrders.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.perPage)
    },
  },
  methods: {
    async fetchOrders() {
      this.loading = true
      this.error = null
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        // Use different endpoint based on status filter
        const endpoint = this.statusFilter
          ? `/vendor/orders/get_all_by_status?status=${this.statusFilter}`
          : '/vendor/orders/get_all'

        const response = await getData(endpoint, headers)
        this.orders = response.orders || []
      } catch (error) {
        this.error = 'فشل في تحميل الطلبات'
        console.error('Error fetching orders:', error)
      } finally {
        this.loading = false
      }
    },

    // async updateOrderStatus(order) {
    //   try {
    //     const token = localStorage.getItem('access_token')
    //     const headers = { Authorization: `Bearer ${token}` }

    //     await putData(
    //       `/vendor/orders/update_status/${order.id}`,
    //       { status: order.status },
    //       headers
    //     )

    //     this.toast.success('تم تحديث حالة الطلب بنجاح')
    //     this.fetchOrders()
    //   } catch (error) {
    //     this.toast.error('فشل في تحديث حالة الطلب')
    //     console.error('Error updating order status:', error)
    //   }
    // },

    showOrderDetails(order) {
      this.selectedOrder = order
    },

    getStatusText(status) {
      const statusMap = {
        pending: 'قيد الانتظار',
        processing: 'قيد المعالجة',
        completed: 'مكتمل',
        cancelled: 'ملغى',
      }
      return statusMap[status] || status
    },

    formatDate(dateString) {
      const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }
      return new Date(dateString).toLocaleDateString('ar-EG', options)
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
      }
    },

    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    },
  },
  created() {
    this.fetchOrders()
  },
}
</script>

<style scoped>
/* General Styles */
.container {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dashboard {
  padding: 2rem;
  background: #f8f9fa;
}

/* Filters Section */
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}

.filter-row {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.status-filter,
.search-input {
  padding: 0.8rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  min-width: 200px;
}

.search-input {
  flex-grow: 1;
  max-width: 400px;
}

.refresh-button {
  background: #3498db;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.refresh-button:hover {
  background: #2980b9;
}

/* Table Styles */
.data-table {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

th,
td {
  padding: 1rem;
  text-align: right;
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

/* Product Info */
.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-name {
  font-weight: 600;
}

.product-attributes {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.attribute {
  background: #f1f1f1;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.more-attributes {
  color: #3498db;
  cursor: pointer;
}

/* Status Badges */
.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  display: inline-block;
}

.status-badge.pending {
  background: #fef5e7;
  color: #f39c12;
}

.status-badge.processing {
  background: #e3f2fd;
  color: #1976d2;
}

.status-badge.completed {
  background: #e8f6f3;
  color: #27ae60;
}

.status-badge.cancelled {
  background: #fdecea;
  color: #e74c3c;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.8rem;
  align-items: center;
}

.details-btn {
  background: #3498db;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s;
}

.details-btn:hover {
  background: #2980b9;
}

.status-select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  cursor: pointer;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.close-modal {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #7f8c8d;
}

.order-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
}

.detail-label {
  font-weight: 600;
  color: #2c3e50;
}

.detail-value {
  color: #7f8c8d;
}

.product-details {
  margin-top: 1rem;
}

.product-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
}

.product-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.product-price {
  font-weight: 600;
  color: #27ae60;
}

.product-quantity {
  color: #7f8c8d;
  margin-bottom: 1rem;
}

.attributes-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.attribute-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.attribute-name {
  font-weight: 500;
}

.attribute-value {
  color: #7f8c8d;
}

.actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.update-status-btn {
  background: #3498db;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.update-status-btn:hover {
  background: #2980b9;
}

/* Pagination Styles */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1rem;
}

.pagination-button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.pagination-button:hover:not(:disabled) {
  background: #3498db;
  color: white;
  border-color: #3498db;
}

.pagination-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #7f8c8d;
  font-size: 0.9rem;
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

/* Responsive Adjustments */
@media (max-width: 768px) {
  .container {
    grid-template-columns: 1fr;
  }

  .filter-row {
    flex-direction: column;
    align-items: stretch;
  }

  .status-filter,
  .search-input {
    width: 100%;
    max-width: none;
  }

  table {
    display: block;
    overflow-x: auto;
  }

  .modal-content {
    width: 95%;
    padding: 1rem;
  }

  .attributes-list {
    grid-template-columns: 1fr;
  }
}
</style>
