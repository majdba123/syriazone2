<template>
  <div class="signup">
    <div class="signup-container">
      <div class="logo">
        <img src="/path/to/logo.png" alt="SyriaZone Logo" />
      </div>
      <h2>Create Account</h2>
      <form @submit.prevent="handleSubmit">
        <div class="input-container">
          <label for="name">Full Name</label>
          <input
            type="text"
            v-model="name"
            id="name"
            placeholder="Enter your full name"
            required
          />
        </div>
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
        <div class="input-container">
          <label for="confirm-password">Confirm Password</label>
          <input
            type="password"
            v-model="confirmPassword"
            id="confirm-password"
            placeholder="Confirm your password"
            required
          />
        </div>
        <button type="submit" class="signup-button">Create Account</button>
      </form>

      <!-- أزرار التسجيل عبر فيسبوك وجيميل -->
      <div class="social-signup">
        <p>or sign up with:</p>
        <button
          class="social-button facebook-button"
          @click="signUpWithFacebook"
        >
          Facebook
        </button>
        <button class="social-button google-button" @click="signUpWithGoogle">
          Google
        </button>
      </div>

      <div class="other-options">
        <p>
          By creating an account, you agree to SyriaZone's
          <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
        </p>
        <p>
          Already have an account?
          <router-link to="/LoginPage" class="signin-link">Sign-In</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { postData } from "@/api";
import { useToast } from "vue-toastification";

export default {
  data() {
    return {
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    };
  },
  methods: {
    async handleSubmit() {
      const toast = useToast();

      if (this.password !== this.confirmPassword) {
        toast.error("Passwords do not match!");
        return;
      }

      if (!this.name || !this.email || !this.password) {
        toast.error("Please fill out all fields correctly.");
        return;
      }

      const userData = {
        name: this.name,
        email: this.email,
        password: this.password,
      };
      try {
        const response = await postData("/api/register", userData);
        console.log(response);
        this.$router.push("/LoginPage");
      } catch (error) {
        console.error("Error posting data:", error);
      }
    },
  },
};
</script>

<style scoped>
/* Basic styling for the signup page */
.signup {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f1f1f1;
}
.social-signup {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 20px;
}

.social-button {
  width: 100%;
  padding: 10px;
  margin: 5px 0;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
}

.facebook-button {
  background-color: #3b5998; /* لون فيسبوك */
}

.google-button {
  background-color: #db4437; /* لون جوجل */
}

.signin-link:hover {
  text-decoration: underline;
}

.signup-container {
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

button.signup-button {
  width: 100%;
  padding: 12px;
  background-color: #f0c14b;
  border-radius: 4px;
  border: 1px solid #a88734;
  font-size: 16px;
  cursor: pointer;
  font-weight: bold;
}

button.signup-button:hover {
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

/* Styling the "Sign In" link */
.signin-link {
  font-weight: bold;
  color: #0073e6;
  text-decoration: none;
}

.signin-link:hover {
  text-decoration: underline;
}
</style>
