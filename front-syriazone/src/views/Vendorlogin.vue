<!-- src/views/Dashboard.vue -->
<template>
  <SideBarvendor />
  <div class="dashboard">
    <h1>مرحبًا بك في لوحة التحكم!</h1>
  </div>
</template>

<script>
import SideBarvendor from "@/components/SideBarvendor.vue";
export default {
  name: "VendorLogin",
  components: {
    SideBarvendor,
  },
};
</script>

<style scoped>
.dashboard {
  padding: 20px;
}
</style>
