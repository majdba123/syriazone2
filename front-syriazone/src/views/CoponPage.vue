<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Form Section -->
      <div class="coupon-form">
        <h2>إدارة الكوبونات</h2>
        <form @submit.prevent="addCoupon">
          <div class="form-row">
            <div class="form-group">
              <label for="coupon-code">كود الكوبون:</label>
              <input
                id="coupon-code"
                v-model="coupon.code"
                type="text"
                required
                class="custom-input"
                placeholder="مثال: SUMMER2023"
              />
            </div>
            <div class="form-group">
              <label for="coupon-discount">نسبة الخصم (%):</label>
              <input
                id="coupon-discount"
                v-model="coupon.discount_percent"
                type="number"
                min="1"
                max="100"
                required
                class="custom-input"
                placeholder="مثال: 15"
              />
            </div>
            <div class="form-group">
              <label for="coupon-expiry">تاريخ الانتهاء:</label>
              <input
                id="coupon-expiry"
                v-model="coupon.expires_at"
                type="date"
                required
                class="custom-input"
              />
            </div>
          </div>
          <button type="submit" class="add-button">
            <span class="icon">+</span> إضافة كوبون
          </button>
        </form>
      </div>

      <!-- Table Section -->
      <div class="data-table">
        <div class="table-header">
          <h3>قائمة الكوبونات</h3>
          <div class="table-controls">
            <select v-model="filterStatus" class="status-filter">
              <option value="">جميع الحالات</option>
              <option value="active">نشط</option>
              <option value="expired">منتهي</option>
            </select>
            <button class="refresh-button" @click="fetchCoupons">
              <span class="icon">↻</span> تحديث
            </button>
          </div>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="filteredCoupons.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>الكود</th>
                <th>نسبة الخصم</th>
                <th>الحالة</th>
                <th>تاريخ الانتهاء</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(coupon, index) in filteredCoupons" :key="coupon.id">
                <td>{{ index + 1 }}</td>
                <td>{{ coupon.code }}</td>
                <td>{{ coupon.discount_percent }}%</td>
                <td>
                  <span :class="['status-badge', statusClass(coupon)]">
                    {{ couponStatus(coupon) }}
                  </span>
                </td>
                <td>{{ formatDate(coupon.expires_at) }}</td>
                <td>
                  <div class="action-buttons">
                    <button class="edit-btn" @click="startEditing(coupon)">
                      ✏️
                    </button>
                    <button
                      class="delete-btn"
                      @click="confirmDelete(coupon.id)"
                    >
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ لا توجد كوبونات متاحة</div>
        </template>

        <!-- Pagination -->
        <div v-if="coupons.data && coupons.data.length > 0" class="pagination">
          <button
            @click="changePage(coupons.current_page - 1)"
            :disabled="coupons.current_page === 1"
          >
            السابق
          </button>
          <span
            >الصفحة {{ coupons.current_page }} من {{ coupons.last_page }}</span
          >
          <button
            @click="changePage(coupons.current_page + 1)"
            :disabled="coupons.current_page === coupons.last_page"
          >
            التالي
          </button>
        </div>

        <!-- Edit Modal -->
        <div v-if="editingCoupon" class="modal-overlay">
          <div class="modal-content">
            <h3>تعديل الكوبون</h3>
            <form @submit.prevent="saveChanges">
              <div class="form-group">
                <label>الكود:</label>
                <input v-model="editingCoupon.code" class="custom-input" />
              </div>
              <div class="form-group">
                <label>نسبة الخصم (%):</label>
                <input
                  v-model="editingCoupon.discount_percent"
                  type="number"
                  min="1"
                  max="100"
                  class="custom-input"
                />
              </div>
              <div class="form-group">
                <label>تاريخ الانتهاء:</label>
                <input
                  v-model="editingCoupon.expires_at"
                  type="date"
                  class="custom-input"
                />
              </div>
              <div class="modal-actions">
                <button type="button" class="cancel-btn" @click="cancelEditing">
                  ✖️ إلغاء
                </button>
                <button type="submit" class="save-btn">✔️ حفظ التغييرات</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from '@/components/SideBar.vue'
import { getData, postData, putData, deleteData } from '@/api'
import { useToast } from 'vue-toastification'

export default {
  name: 'CouponPage',
  components: {
    SideBar,
  },
  setup() {
    const toast = useToast()
    return { toast }
  },
  data() {
    return {
      coupon: {
        code: '',
        discount_percent: '',
        expires_at: '',
      },
      coupons: {
        data: [],
        current_page: 1,
        last_page: 1,
      },
      filterStatus: '',
      loading: false,
      error: null,
      editingCoupon: null,
    }
  },
  computed: {
    filteredCoupons() {
      if (!this.filterStatus) return this.coupons.data || []

      const now = new Date().toISOString().split('T')[0]
      return this.coupons.data.filter((coupon) => {
        if (this.filterStatus === 'active') {
          return coupon.expires_at >= now
        } else if (this.filterStatus === 'expired') {
          return coupon.expires_at < now
        }
        return true
      })
    },
  },
  methods: {
    statusClass(coupon) {
      const now = new Date().toISOString().split('T')[0]
      return {
        active: coupon.expires_at >= now,
        expired: coupon.expires_at < now,
      }
    },
    couponStatus(coupon) {
      const now = new Date().toISOString().split('T')[0]
      return coupon.expires_at >= now ? 'نشط' : 'منتهي'
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(dateString).toLocaleDateString('ar-EG', options)
    },
    async fetchCoupons(page = 1) {
      this.loading = true
      this.error = null
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }
        const response = await getData(
          `/admin/coupons/index?page=${page}`,
          headers
        )
        this.coupons = response.data
      } catch (error) {
        this.error = 'فشل في تحميل الكوبونات'
        console.error(error)
      } finally {
        this.loading = false
      }
    },
    async addCoupon() {
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        await postData(
          '/admin/coupons/store',
          {
            code: this.coupon.code,
            discount_percent: this.coupon.discount_percent,
            expires_at: this.coupon.expires_at,
          },
          headers
        )

        this.toast.success('تم إضافة الكوبون بنجاح')
        this.coupon = { code: '', discount_percent: '', expires_at: '' }
        await this.fetchCoupons()
      } catch (error) {
        this.toast.error('فشل في إضافة الكوبون')
        console.error(error)
      }
    },
    startEditing(coupon) {
      this.editingCoupon = { ...coupon }
    },
    cancelEditing() {
      this.editingCoupon = null
    },
    async saveChanges() {
      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        await putData(
          `/admin/coupons/update/${this.editingCoupon.id}`,
          {
            code: this.editingCoupon.code,
            discount_percent: this.editingCoupon.discount_percent,
            expires_at: this.editingCoupon.expires_at,
          },
          headers
        )

        this.toast.success('تم تحديث الكوبون بنجاح')
        this.editingCoupon = null
        await this.fetchCoupons(this.coupons.current_page)
      } catch (error) {
        this.toast.error('فشل في تحديث الكوبون')
        console.error(error)
      }
    },
    async confirmDelete(id) {
      if (!confirm('هل أنت متأكد من حذف هذا الكوبون؟')) return

      try {
        const token = localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }

        await deleteData(`/admin/coupons/delete/${id}`, headers)
        this.toast.success('تم حذف الكوبون بنجاح')
        await this.fetchCoupons(this.coupons.current_page)
      } catch (error) {
        this.toast.error('فشل في حذف الكوبون')
        console.error(error)
      }
    },
    changePage(page) {
      if (page >= 1 && page <= this.coupons.last_page) {
        this.fetchCoupons(page)
      }
    },
  },
  created() {
    this.fetchCoupons()
  },
}
</script>

<style scoped>
/* التنسيقات الأساسية تبقى كما هي مع بعض التعديلات البسيطة */

.dashboard {
  padding: 2rem;
  background: #f8f9fa;
}

.coupon-form {
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

.table-controls {
  display: flex;
  gap: 1rem;
}

.status-filter {
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #ddd;
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
  text-align: right;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: 600;
}

tr:hover {
  background: #f8f9fa;
}

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-badge.active {
  background: #e8f6f3;
  color: #27ae60;
}

.status-badge.expired {
  background: #fdecea;
  color: #e74c3c;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn {
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

button:hover {
  transform: scale(1.1);
}

/* Pagination Styles */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination span {
  color: #7f8c8d;
}
.container {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
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

.custom-input:focus,
.custom-select:focus {
  border-color: #3498db;
  outline: none;
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

.table-controls {
  display: flex;
  gap: 1rem;
}

.status-filter {
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #ddd;
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

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
}

.status-badge.pending {
  background: #fef5e7;
  color: #f39c12;
}

.status-badge.active {
  background: #e8f6f3;
  color: #27ae60;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.edit-btn,
.delete-btn,
.stats-btn {
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
.stats-btn {
  background: #9b59b6;
  color: white;
}

button:hover {
  transform: scale(1.1);
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.stats-modal {
  width: 90%;
  max-width: 800px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin: 1.5rem 0;
}

.stat-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
}

.stat-card h4 {
  margin-top: 0;
  color: #7f8c8d;
}

.stat-card p {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0;
  color: #2c3e50;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.close-btn {
  background: #95a5a6;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 1.5rem;
  width: 100%;
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

/* باقي التنسيقات تبقى كما هي */
</style>
