<template>
  <div class="header">
    <div class="logo">LOGO</div>
    <div class="location">LOCATION</div>
    <div class="search">
      <input type="text" placeholder="Search for products, brands and more" />
      <button>Search</button>
    </div>
    <div class="language">Language</div>
    <div class="account" @click="goToLogin">Account</div>
    <div class="order">Order</div>
    <div class="cart">Cart</div>
  </div>
</template>

<script>
export default {
  name: "HeaderPage",
  data() {
    return {};
  },
  methods: {
    goToLogin() {
      this.$router.push("LoginPage");
    },
  },
};
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
}

/* Header Styling */
.header {
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 10px 10px;
  background-color: #131921;
  color: white;
}

.header .logo {
  font-size: 24px;
  font-weight: bold;
  cursor: pointer;
}

/* Styling for Header Items */
.header .location,
.header .language,
.header .account,
.header .order,
.header .cart {
  margin: 0 10px;
  cursor: pointer;
  padding: 8px;
  transition: background-color 0.9s ease, color 0.3s ease;
}

/* Hover Effects */
.header .location:hover,
.header .language:hover,
.header .account:hover,
.header .order:hover,
.header .cart:hover {
  background-color: #febd69;
  color: #131921;
  border-radius: 4px;
}

/* Search Bar */
.header .search {
  display: flex;
  align-items: center;
  flex: 1;
  max-width: 500px;
  background-color: #fff;
  border-radius: 4px;
  padding: 5px;
}

.header .search input {
  flex: 1;
  border: none;
  padding: 5px;
  font-size: 16px;
  border-radius: 4px;
}

.header .search button {
  background-color: #febd69;
  border: none;
  padding: 8px 16px;
  margin-left: 5px;
  cursor: pointer;
  font-weight: bold;
  color: #111;
  border-radius: 4px;
}

.header .search input:focus {
  outline: none;
}

/* Cart Icon Style */
.header .cart {
  position: relative;
}

.header .cart::after {
  content: "0";
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: red;
  color: white;
  font-size: 12px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: center;
  }
  .header .search {
    width: 100%;
    margin-bottom: 10px;
  }
}
</style>
