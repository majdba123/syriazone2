<template>
  <div class="admin-dashboard">
    <SideBar />
    <main class="main-content">
      <div class="header">
        <h1>مرحبًا بك في لوحة التحكم</h1>
        <p>نظرة عامة على إحصائيات النظام</p>
      </div>

      <!-- بطاقات الإحصائيات -->
      <div class="stats-grid">
        <StatCard
          title="إجمالي المستخدمين"
          :value="stats.total_users"
          icon="mdi-account-group"
          color="blue"
        />
        <StatCard
          title="البائعون النشطون"
          :value="stats.active_vendors"
          icon="mdi-store-check"
          color="green"
        />
        <StatCard
          title="إجمالي المنتجات"
          :value="stats.total_products"
          icon="mdi-package-variant"
          color="orange"
        />
        <StatCard
          title="إجمالي الطلبات"
          :value="stats.total_orders"
          icon="mdi-cart"
          color="purple"
        />
        <StatCard
          title="المبيعات الإجمالية"
          :value="`${stats.total_sales} $`"
          icon="mdi-currency-usd"
          color="teal"
        />
        <StatCard
          title="العمولات المعلقة"
          :value="`${stats.pending_commissions} $`"
          icon="mdi-cash-clock"
          color="red"
        />
      </div>

      <!-- الرسوم البيانية -->
      <div class="charts-row">
        <div class="chart-container">
          <h3>توزيع البائعين</h3>
          <PieChart :chart-data="vendorsChartData" :options="chartOptions" />
        </div>
        <div class="chart-container">
          <h3>حالة الطلبات</h3>
          <BarChart :chart-data="ordersChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- أحدث البائعين -->
      <div class="recent-section">
        <h2>أحدث البائعين</h2>
        <div class="table-container">
          <table class="recent-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الحالة</th>
                <th>تاريخ التسجيل</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="vendor in recentVendors" :key="vendor.id">
                <td>{{ vendor.id }}</td>
                <td>{{ vendor.user.name }}</td>
                <td>{{ vendor.user.email }}</td>
                <td>
                  <span :class="`status-badge ${vendor.status}`">
                    {{ vendor.status === 'active' ? 'نشط' : 'معلق' }}
                  </span>
                </td>
                <td>{{ formatDate(vendor.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import SideBar from '@/components/SideBar.vue'
import { getData } from '@/api'
import StatCard from '@/components/StatCard.vue'
import PieChart from '@/components/charts/PieChart.vue'
import BarChart from '@/components/charts/BarChart.vue'

export default {
  name: 'AdminPage',
  components: {
    SideBar,
    StatCard,
    PieChart,
    BarChart,
  },
  setup() {
    const stats = ref({})
    const recentVendors = ref([])
    const recentOrders = ref([])
    const isLoading = ref(true)

    // بيانات الرسوم البيانية
    // في setup() الخاص بـ AdminPage.vue
    const vendorsChartData = ref({
      labels: ['نشط', 'معلق', 'محظور'],
      datasets: [
        {
          data: [
            stats.value.active_vendors,
            stats.value.pending_vendors,
            stats.value.banned_vendors,
          ],
          backgroundColor: ['#4CAF50', '#FFC107', '#F44336'],
        },
      ],
    })

    const ordersChartData = ref({
      labels: ['مكتمل', 'معلق', 'ملغي'],
      datasets: [
        {
          label: 'الطلبات',
          data: [
            stats.value.completed_orders,
            stats.value.pending_orders,
            stats.value.cancelled_orders,
          ],
          backgroundColor: '#3f51b5',
        },
      ],
    })

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          rtl: true,
        },
      },
    }

    const fetchDashboardData = async () => {
      try {
        const token = window.localStorage.getItem('access_token')
        const headers = { Authorization: `Bearer ${token}` }
        const data = await getData('/admin/dashboard', headers)
        stats.value = data.stats
        recentVendors.value = data.recent_vendors
        recentOrders.value = data.recent_orders

        // تحديث بيانات الرسوم البيانية
        vendorsChartData.value.datasets[0].data = [
          data.stats.active_vendors,
          data.stats.pending_vendors,
          data.stats.banned_vendors,
        ]

        ordersChartData.value.datasets[0].data = [
          data.stats.completed_orders,
          data.stats.pending_orders,
          data.stats.cancelled_orders,
        ]

        isLoading.value = false
      } catch (error) {
        console.error('Failed to fetch dashboard data:', error)
      }
    }

    const formatDate = (dateString) => {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(dateString).toLocaleDateString('ar-EG', options)
    }

    onMounted(() => {
      fetchDashboardData()
    })

    return {
      stats,
      recentVendors,
      recentOrders,
      isLoading,
      vendorsChartData,
      ordersChartData,
      chartOptions,
      formatDate,
    }
  },
}
</script>

<style scoped>
.admin-dashboard {
  display: grid;
  grid-template-columns: 14rem auto;
  gap: 1.8rem;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  padding: 20px;
  margin-right: 280px; /* يتناسب مع عرض السايدبار */
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  color: #333;
  font-size: 28px;
  margin-bottom: 5px;
}

.header p {
  color: #666;
  font-size: 16px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.chart-container {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  height: 350px;
}

.chart-container h3 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #444;
  text-align: center;
}

.recent-section {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.recent-section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #444;
}

.table-container {
  overflow-x: auto;
}

.recent-table {
  width: 100%;
  border-collapse: collapse;
}

.recent-table th,
.recent-table td {
  padding: 12px 15px;
  text-align: right;
  border-bottom: 1px solid #eee;
}

.recent-table th {
  background-color: #f8f9fa;
  color: #555;
  font-weight: 600;
}

.status-badge {
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background-color: #e6f7ee;
  color: #00a854;
}

.status-badge.pending {
  background-color: #fff7e6;
  color: #fa8c16;
}

@media (max-width: 768px) {
  .main-content {
    margin-right: 0;
    padding: 15px;
  }

  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }

  .charts-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
