<!-- src/views/Dashboard.vue -->
<template>
  <SideBar />
  <div class="dashboard">
    <h1>مرحبًا بك في لوحة التحكم!</h1>
  </div>
</template>

<script>
import SideBar from "@/components/SideBar.vue";
export default {
  name: "AdminPage",
  components: {
    SideBar,
  },
};
</script>

<style scoped>
.dashboard {
  padding: 20px;
}
</style>
