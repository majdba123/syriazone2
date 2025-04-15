<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <!-- Form Section -->
      <div class="vendor-form">
        <h2>Manage Vendors</h2>
        <form @submit.prevent="addVendor">
          <div class="form-row">
            <div class="form-group">
              <label for="vendor-name">Name:</label>
              <input
                id="vendor-name"
                v-model="vendor.name"
                type="text"
                required
                class="custom-input"
              />
            </div>
            <div class="form-group">
              <label for="vendor-email">Email:</label>
              <input
                id="vendor-email"
                v-model="vendor.email"
                type="email"
                required
                class="custom-input"
              />
            </div>
            <div class="form-group">
              <label for="vendor-password">Password:</label>
              <input
                id="vendor-password"
                v-model="vendor.password"
                type="password"
                required
                class="custom-input"
              />
            </div>
          </div>
          <button type="submit" class="add-button">
            <span class="icon">+</span> Add Vendor
          </button>
        </form>
      </div>

      <!-- Table Section -->
      <div class="data-table">
        <div class="table-header">
          <h3>Vendors List</h3>
          <div class="table-controls">
            <select v-model="filterStatus" class="status-filter">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="pand">Pand</option>
            </select>
            <button class="refresh-button" @click="fetchVendors">
              <span class="icon">↻</span> Refresh
            </button>
          </div>
        </div>

        <div v-if="loading" class="loading-overlay">
          <div class="spinner"></div>
        </div>

        <div v-else-if="error" class="error-message">⚠️ {{ error }}</div>

        <template v-else>
          <table v-if="filteredVendors.length > 0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(vendor, index) in filteredVendors" :key="vendor.id">
                <td>{{ index + 1 }}</td>
                <td>{{ vendor.name }}</td>
                <td>{{ vendor.email }}</td>
                <td>
                  <span :class="['status-badge', statusClass(vendor.status)]">
                    {{ vendor.status }}
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="edit-btn" @click="startEditing(vendor)">
                      ✏️
                    </button>
                    <!-- <button
                      class="delete-btn"
                      @click="confirmDelete(vendor.id)"
                    >
                      🗑️
                    </button> -->
                    <button class="stats-btn" @click="showStats(vendor.id)">
                      📊
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="no-data">ℹ️ No vendors found</div>
        </template>

        <!-- Edit Modal -->
        <div v-if="editingVendor" class="modal-overlay">
          <div class="modal-content">
            <h3>Edit Vendor</h3>
            <form @submit.prevent="saveChanges">
              <div class="form-group">
                <label>Name:</label>
                <input v-model="editingVendor.name" class="custom-input" />
              </div>
              <div class="form-group">
                <label>Email:</label>
                <input
                  v-model="editingVendor.email"
                  type="email"
                  class="custom-input"
                />
              </div>
              <div class="form-group">
                <label>Status:</label>
                <select v-model="editingVendor.status" class="custom-select">
                  <option value="pending">Pending</option>
                  <option value="active">Active</option>
                  <option value="pand">Pand</option>
                </select>
              </div>
              <div class="modal-actions">
                <button type="button" class="cancel-btn" @click="cancelEditing">
                  ✖️ Cancel
                </button>
                <button type="submit" class="save-btn">✔️ Save Changes</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Stats Modal -->
        <div v-if="selectedStats" class="modal-overlay">
          <div class="modal-content stats-modal">
            <h3>Vendor Statistics</h3>
            <div class="stats-grid">
              <div class="stat-card">
                <h4>Completed Orders</h4>
                <p>{{ selectedStats.completed_orders }}</p>
              </div>
              <div class="stat-card">
                <h4>Pending Orders</h4>
                <p>{{ selectedStats.pending_orders }}</p>
              </div>
              <div class="stat-card">
                <h4>Total Sales</h4>
                <p>${{ selectedStats.total_sales_complete }}</p>
              </div>
              <div class="stat-card">
                <h4>Total Commissions</h4>
                <p>${{ selectedStats.total_commissions_complete }}</p>
              </div>
              <div class="stat-card">
                <h4>Balance</h4>
                <p>${{ selectedStats.balance }}</p>
              </div>
            </div>
            <button class="close-btn" @click="selectedStats = null">
              ✖️ Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
import { getData, postData, putData } from "@/api";
import { useToast } from "vue-toastification";

export default {
  name: "VendorPage",
  components: {
    SideBar,
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  data() {
    return {
      vendor: {
        name: "",
        email: "",
        password: "",
      },
      vendors: [],
      filterStatus: "",
      loading: false,
      error: null,
      editingVendor: null,
      selectedStats: null,
    };
  },
  computed: {
    filteredVendors() {
      if (!Array.isArray(this.vendors)) return [];
      if (!this.filterStatus) return this.vendors;
      return this.vendors.filter((v) => v.status === this.filterStatus);
    },
  },
  methods: {
    statusClass(status) {
      return {
        pending: status === "pending",
        active: status === "active",
        pand: status === "pand",
      };
    },
    async fetchVendors() {
      this.loading = true;
      this.error = null;
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const response = await getData(
          "/admin/vendores/get_by_status",
          headers
        );
        // Ensure we're extracting the array from the response
        this.vendors = response.data || []; // Adjust according to actual response structure
      } catch (error) {
        this.error = "Failed to load vendors";
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    async addVendor() {
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };

        await postData(
          "/admin/vendores/store",
          {
            name: this.vendor.name,
            email: this.vendor.email,
            password: this.vendor.password,
          },
          headers
        );

        this.toast.success("Vendor added successfully");
        this.vendor = { name: "", email: "", password: "" };
        await this.fetchVendors();
      } catch (error) {
        this.toast.error("Failed to add vendor");
        console.error(error);
      }
    },
    startEditing(vendor) {
      this.editingVendor = { ...vendor };
    },
    cancelEditing() {
      this.editingVendor = null;
    },
    async saveChanges() {
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };

        await putData(
          `/admin/vendores/update/${this.editingVendor.id}`,
          {
            name: this.editingVendor.name,
            email: this.editingVendor.email,
            status: this.editingVendor.status,
          },
          headers
        );

        this.toast.success("Vendor updated successfully");
        this.editingVendor = null;
        await this.fetchVendors();
      } catch (error) {
        this.toast.error("Failed to update vendor");
        console.error(error);
      }
    },
    // async confirmDelete(id) {
    //   if (!confirm("Are you sure you want to delete this vendor?")) return;

    //   try {
    //     const token = localStorage.getItem("access_token");
    //     const headers = { Authorization: `Bearer ${token}` };

    //     await deleteData(`/admin/vendores/delete/${id}`, headers);
    //     this.toast.success("Vendor deleted successfully");
    //     await this.fetchVendors();
    //   } catch (error) {
    //     this.toast.error("Failed to delete vendor");
    //     console.error(error);
    //   }
    // },
    async showStats(id) {
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const response = await getData(
          `/admin/vendores/get_statical_commission/${id}`,
          headers
        );
        this.selectedStats = response.stats;
      } catch (error) {
        this.toast.error("Failed to load vendor statistics");
        console.error(error);
      }
    },
  },
  created() {
    this.fetchVendors();
  },
};
</script>

<style scoped>
/* تطابق تنسيقات الصفحات السابقة */
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

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
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
</style>
