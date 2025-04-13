<template>
  <div class="Header"><HeaderPage /></div>

  <div class="home">
    <img alt="Vue logo" src="../assets/logo.png" />
    <HelloWorld msg="Welcome to Your Vue.js App" />
  </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from "@/components/HelloWorld.vue";
import HeaderPage from "@/components/Header/HeaderPage.vue";

export default {
  name: "HomeView",
  components: {
    HelloWorld,
    HeaderPage,
  },
};
</script>
