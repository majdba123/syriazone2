<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <div class="category-form">
        <h2>Add New Category</h2>
        <form @submit.prevent="addCategory">
          <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" v-model="title" required />
          </div>
          <div class="form-group">
            <label for="percent">Percent:</label>
            <input type="number" id="percent" v-model="percent" required />
          </div>
          <button type="submit" class="add-button">Add Category</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
import { postData } from "@/api";

export default {
  name: "AddCategory",
  components: {
    SideBar,
  },
  data() {
    return {
      title: "",
      percent: "",
    };
  },
  methods: {
    async addCategory() {
      const token = window.localStorage.getItem("access_token");

      const headers = {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      };

      const userData = {
        title: String(this.title),
        percent: String(this.percent),
      };

      try {
        const response = await postData(
          "/admin/categories/store",
          userData,
          headers
        );
        console.log("Category added successfully:", response);
      } catch (error) {
        console.error("Error posting data:", error);
      }
    },
  },
};
</script>

<style scoped>
.container {
  display: grid;
  width: 100%;
  gap: 1.8rem;
  grid-template-columns: 14rem auto;
  margin-left: 0;
  height: 100vh;
  overflow-y: auto;
}

.dashboard {
  flex-grow: 1;
  padding: 20px;
}

.category-form {
  margin-top: 20px;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: #f9f9f9;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.category-form h2 {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input {
  width: calc(100% - 10px);
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.add-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.add-button:hover {
  background-color: #2980b9;
  transform: scale(1.05);
}
</style>
