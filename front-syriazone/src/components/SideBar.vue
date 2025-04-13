<template>
  <div>
    <button @click="toggleSidebar" class="toggle-button">
      <i class="fas" :class="isSidebarOpen ? 'fa-times' : 'fa-bars'"></i>
    </button>
    <div class="sidebar" :class="{ 'is-open': isSidebarOpen }">
      <div class="sidebar-header">
        <h2>Dashboard</h2>
      </div>
      <ul class="sidebar-menu">
        <li>
          <router-link to="/dashboard" class="menu-item">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </router-link>
        </li>
        <li>
          <router-link to="/AddCategory" class="menu-item">
            <i class="fas fa-users"></i>
            Category
          </router-link>
        </li>
        <li>
          <router-link to="/AddSubCategory" class="menu-item">
            <i class="fas fa-box"></i>
            Sub Category
          </router-link>
        </li>
        <li>
          <router-link to="/orders" class="menu-item">
            <i class="fas fa-shopping-cart"></i>
            Orders
          </router-link>
        </li>
        <li>
          <router-link to="/settings" class="menu-item">
            <i class="fas fa-cog"></i>
            Settings
          </router-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: "SideBar",
  data() {
    return {
      isSidebarOpen: true,
    };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
  },
};
</script>

<style scoped>
.sidebar {
  width: 250px;
  background-color: #2c3e50;
  color: white;
  height: 100vh;
  padding: 20px;
  position: fixed;
  transition: transform 0.3s ease;
  transform: translateX(0);
}

.sidebar.is-open {
  transform: translateX(0);
}

.sidebar:not(.is-open) {
  transform: translateX(-100%);
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
  z-index: 1000; /* To ensure it is above the sidebar */
}

.sidebar-header {
  text-align: center;
  margin-bottom: 20px;
}

.sidebar-header h2 {
  color: #ecf0f1;
}

.sidebar-menu {
  list-style-type: none;
  padding: 0;
}

.sidebar-menu li {
  margin: 15px 0;
}

.menu-item {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  padding: 10px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.menu-item i {
  margin-right: 10px; /* Space between icon and text */
}

.menu-item:hover {
  background-color: #34495e; /* Darker shade on hover */
}

/* Media Queries for Responsiveness */
@media (max-width: 768px) {
  .sidebar {
    width: 200px; /* Smaller width on mobile */
    padding: 10px; /* Less padding on mobile */
  }

  .toggle-button {
    left: calc(
      200px + 10px
    ); /* Position the toggle button next to the sidebar */
    top: calc(20px + (20px / 2)); /* Center the button vertically */
    z-index: 1001; /* Ensure it's above other elements */
  }
}
</style>
