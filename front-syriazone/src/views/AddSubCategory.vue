<template>
  <div class="container">
    <SideBar />
    <div class="dashboard">
      <div class="category-form">
        <h2>Add New Sub-Category</h2>
        <form @submit.prevent="addSubCategory">
          <!-- اختيار الفئة -->
          <div class="form-group">
            <label for="category_id">Category:</label>
            <select v-model="category_id" id="category_id" required>
              <option value="" disabled>Select a category</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.title }}
              </option>
            </select>
          </div>

          <!-- حقل النسبة -->
          <div class="form-group">
            <label for="percent">Percent:</label>
            <input type="text" id="percent" v-model="percent" required />
          </div>

          <button type="submit" class="add-button">Add Sub-Category</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
import { getData, postData } from "@/api";

export default {
  name: "AddSubCategory",
  components: {
    SideBar,
  },
  data() {
    return {
      percent: "",
      category_id: "",
      categories: [],
    };
  },
  mounted() {
    this.fetchCategories();
    // this.fetchGovernment();
  },
  methods: {
    // fetchGovernment() {
    //   const access_token = window.localStorage.getItem("access_token");
    //   axios({
    //     method: "get",
    //     url: "http://127.0.0.1:8000/admin/categories/get_all",
    //     headers: { Authorization: `Bearer ${access_token}` },
    //   })
    //     .then((response) => {
    //       this.categories = response.data;
    //       console.log(response);
    //     })
    //     .catch((error) => {
    //       console.log("Error getting Path", error);
    //     });
    // },
    async fetchCategories() {
      const token = window.localStorage.getItem("access_token");

      const headers = {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      };
      console.log(headers);
      try {
        const response = await getData("/admin/categories/get_all", headers);
        this.categories = response;
        console.log(response);
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    },

    async addSubCategory() {
      const token = window.localStorage.getItem("access_token");

      const headers = {
        Authorization: `Bearer ${token}`,
      };

      const subCategoryData = {
        category_id: 2,
        name: String(this.percent),
      };
      console.log(subCategoryData);

      try {
        const response = await postData(
          "/admin/subcategories/store",
          subCategoryData,
          headers
        );
        console.log("Sub-category added successfully:", response);
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
