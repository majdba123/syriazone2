<template>
  <div>
    <button @click="toggleSidebar" class="toggle-button">
      <i class="fas" :class="isSidebarOpen ? 'fa-times' : 'fa-bars'"></i>
    </button>
    <div
      class="sidebar"
      :class="{ 'is-open': isSidebarOpen }"
      @mouseenter="hoverOpen = true"
      @mouseleave="hoverOpen = false"
    >
      <div class="sidebar-header">
        <h2>Dashboard</h2>
      </div>
      <ul class="sidebar-menu">
        <li>
          <router-link to="/CategoryVendor" class="menu-item">
            <i class="fas fa-tags"></i>
            <span class="menu-text">Category</span>
          </router-link>
        </li>
        <li>
          <router-link to="/SubcategoryVendor" class="menu-item">
            <i class="fas fa-tag"></i>
            <span class="menu-text">Sub Category</span>
          </router-link>
        </li>
        <li>
          <router-link to="/AddProduct" class="menu-item">
            <i class="fas fa-store"></i>
            <span class="menu-text">Product</span>
          </router-link>
        </li>
        <li>
          <router-link to="/products" class="menu-item">
            <i class="fas fa-box-open"></i>
            <span class="menu-text">aa</span>
          </router-link>
        </li>
        <li>
          <router-link to="/orders" class="menu-item">
            <i class="fas fa-shopping-cart"></i>
            <span class="menu-text">Orders</span>
          </router-link>
        </li>
        <li>
          <router-link to="/settings" class="menu-item">
            <i class="fas fa-cog"></i>
            <span class="menu-text">Settings</span>
          </router-link>
        </li>
      </ul>

      <div class="logout-section">
        <button @click="logout" class="logout-button">
          <i class="fas fa-sign-out-alt"></i>
          <span class="logout-text">Logout</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { postData } from "@/api";
import { useToast } from "vue-toastification";

export default {
  name: "SideBar",
  data() {
    return {
      isSidebarOpen: false,
      hoverOpen: false,
    };
  },
  setup() {
    const toast = useToast();
    return { toast };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
    async logout() {
      try {
        const token = localStorage.getItem("access_token");
        const headers = { Authorization: `Bearer ${token}` };
        const data = null;
        await postData("/api/logout", data, headers);

        localStorage.removeItem("access_token");
        this.$router.push("/LoginPage");
        this.toast.success("Logged out successfully");
      } catch (error) {
        this.toast.error("Failed to logout");
        console.error("Logout error:", error);
      }
    },
  },
  computed: {
    sidebarVisible() {
      return this.isSidebarOpen || this.hoverOpen;
    },
  },
};
</script>

<style scoped>
.sidebar {
  width: 70px;
  background-color: #2c3e50;
  color: white;
  height: 100vh;
  padding: 20px 10px;
  position: fixed;
  transition: all 0.3s ease;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 999;
  overflow: hidden;
}

.sidebar.is-open,
.sidebar:hover {
  width: 250px;
}

.toggle-button {
  background-color: #2c3e50;
  color: white;
  border: none;
  cursor: pointer;
  font-size: 24px;
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1000;
  transition: all 0.3s ease;
  padding: 5px 10px;
  border-radius: 4px;
}

.toggle-button:hover {
  background-color: #34495e;
}

.sidebar-header {
  text-align: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}

.sidebar-header h2 {
  color: #ecf0f1;
  font-size: 1.5rem;
  margin: 0;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar.is-open .sidebar-header h2,
.sidebar:hover .sidebar-header h2 {
  opacity: 1;
}

.sidebar-menu {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu li {
  margin: 8px 0;
}

.menu-item {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 5px;
  transition: all 0.3s ease;
  font-size: 1rem;
  white-space: nowrap;
}

.menu-item i {
  margin-right: 20px;
  width: 20px;
  text-align: center;
  font-size: 1.2rem;
}

.menu-text {
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar.is-open .menu-text,
.sidebar:hover .menu-text {
  opacity: 1;
}

.menu-item:hover {
  background-color: #34495e;
  transform: translateX(5px);
}

.menu-item.router-link-exact-active {
  background-color: #3498db;
  color: white;
}

.logout-section {
  position: absolute;
  bottom: 20px;
  width: calc(100% - 20px);
  padding-top: 15px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.logout-button {
  background: transparent;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 5px;
  transition: all 0.3s ease;
  font-size: 1rem;
  width: 100%;
  white-space: nowrap;
}

.logout-button i {
  margin-right: 20px;
  width: 20px;
  text-align: center;
  font-size: 1.2rem;
}

.logout-text {
  opacity: 0;
  transition: opacity 0.3s ease;
}

.sidebar.is-open .logout-text,
.sidebar:hover .logout-text {
  opacity: 1;
}

.logout-button:hover {
  background-color: #e74c3c;
  transform: translateX(5px);
}

/* Media Queries for Responsiveness */
@media (max-width: 768px) {
  .sidebar {
    width: 0;
    padding: 20px 0;
  }

  .sidebar.is-open {
    width: 220px;
    padding: 20px 15px;
  }

  .sidebar:hover {
    width: 220px;
    padding: 20px 15px;
  }

  .toggle-button {
    left: 10px;
    top: 10px;
  }
}
</style>
