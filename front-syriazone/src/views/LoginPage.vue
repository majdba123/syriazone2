<template>
  <div class="login">
    <div class="login-container">
      <div class="logo">
        <img src="/path/to/logo.png" alt="SyriaZone Logo" />
      </div>
      <h2>Sign-In</h2>
      <form @submit.prevent="handleSubmit">
        <div class="input-container">
          <label for="email">Email or mobile phone number</label>
          <input
            type="email"
            v-model="email"
            id="email"
            placeholder="Email or mobile phone number"
            required
          />
        </div>
        <div class="input-container">
          <label for="password">Password</label>
          <input
            type="password"
            v-model="password"
            id="password"
            placeholder="Enter password"
            required
          />
        </div>
        <button type="submit" class="signin-button">Sign-In</button>
      </form>
      <div class="other-options">
        <p>
          By signing in, you agree to SyriaZone's
          <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
        </p>
        <p>
          New to SyriaZone?
          <router-link to="/signup" class="create-account"
            >Create your account</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: "",
      password: "",
    };
  },
  methods: {
    handleSubmit() {
      // Logic to validate user credentials and proceed (Here we're just logging for now)
      if (this.email && this.password) {
        // Normally, here you would verify the credentials and log the user in.
        // For now, we'll just log the user in (or you can redirect them to another page).
        this.$router.push("/home"); // Redirecting to the home page after login
      } else {
        alert("Please enter valid credentials");
      }
    },
  },
};
</script>

<style scoped>
/* Basic styling for the login page */
.login {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f1f1f1;
}

.login-container {
  background-color: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.logo img {
  width: 120px;
  margin-bottom: 20px;
}

h2 {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 20px;
  text-align: center;
}

.input-container {
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

button.signin-button {
  width: 100%;
  padding: 12px;
  background-color: #f0c14b;
  border-radius: 4px;
  border: 1px solid #a88734;
  font-size: 16px;
  cursor: pointer;
  font-weight: bold;
}

button.signin-button:hover {
  background-color: #e2a700;
}

.other-options {
  margin-top: 20px;
  text-align: center;
}

a {
  color: #0073e6;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}

/* Styling the "Create account" link */
.create-account {
  font-weight: bold;
  color: #0073e6;
  text-decoration: none;
}

.create-account:hover {
  text-decoration: underline;
}
</style>
