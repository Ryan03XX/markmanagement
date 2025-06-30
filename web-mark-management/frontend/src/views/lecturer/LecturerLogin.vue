<template>
  <div class="login-page">
    <div class="login-card">
      <img src="@/assets/utm-logo.png" alt="School Logo" class="logo" />
      <h2>Lecturer Login</h2>
      <form @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Password" required />
        <button type="submit">Login</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
      <p class="forgot-password">
        <router-link to="/forgot-password">Forgot Password?</router-link>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: '',
      error: ''
    }
  },
  methods: {
    async login() {
      try {
        const res = await fetch('http://localhost:8080/api/login/staff', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: this.email,
            password: this.password
          })
        });

        const result = await res.json();
        if (result.success && result.user.role === 'lecturer') {
          localStorage.setItem('user_id', result.user.id);
          localStorage.setItem('role', result.user.role);
          localStorage.setItem('token', result.token);
          this.$router.push('/lecturer/dashboard');
        } else {
          this.error = 'Invalid credentials or not a lecturer';
        }
      } catch (err) {
        this.error = 'Network error';
        console.error(err);
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(to bottom right, #e0eafc, #cfdef3);
  font-family: Arial, sans-serif;
}

.login-card {
  background-color: white;
  padding: 40px 30px;
  border-radius: 10px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  text-align: center;
  width: 350px;
}

.logo {
  width: auto;
  height: 80px;
  margin-bottom: 15px;
}

h2 {
  color: #003366;
  margin-bottom: 25px;
}

input {
  width: 90%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #0055a5;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}

button:hover {
  background-color: #003f7d;
}

.error {
  color: red;
  margin-top: 10px;
}

.forgot-password {
  margin-top: 10px;
}

.forgot-password a {
  font-size: 14px;
  color: #0055a5;
  text-decoration: underline;
}
</style>
<!-- <template>
  <div class="login-page">
    <div class="login-card">
      <img src="@/assets/utm-logo.png" alt="UTM Logo" class="logo" />
      <h2>Lecturer Login</h2>

      <form @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Password" required />
        <button type="submit">Login</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>

      <p class="forgot-password">
        <router-link to="/forgot-password">Forgot Password?</router-link>
      </p>

      <div class="role-links">
        <router-link to="/">Student Login</router-link>
        <router-link to="/admin/login">Admin Login</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      email: '',
      password: '',
      error: ''
    }
  },
  methods: {
    async login() {
      try {
        const res = await axios.post('http://localhost:8080/api/login/staff', {
          email: this.email,
          password: this.password
        })
        const user = res.data.user
        if (res.data.success && user.role === 'lecturer') {
          localStorage.setItem('user_id', user.id)
          localStorage.setItem('role', user.role)
          localStorage.setItem('token', res.data.token)
          this.$router.push('/lecturer/dashboard')
        } else {
          this.error = 'Invalid lecturer credentials.'
        }
      } catch (err) {
        this.error = 'Login failed. Please try again.'
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(to bottom right, #e0eafc, #cfdef3);
  font-family: Arial, sans-serif;
}

.login-card {
  background-color: white;
  padding: 40px 30px;
  border-radius: 10px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  text-align: center;
  width: 350px;
}

.logo {
  width: auto;
  height: 80px;
  margin-bottom: 15px;
}

h2 {
  color: #003366;
  margin-bottom: 25px;
}

input {
  width: 90%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #0055a5;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: pointer;
}

button:hover {
  background-color: #003f7d;
}

.error {
  color: red;
  margin-top: 10px;
}

.role-links {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 10px;
}

.role-links a {
  padding: 10px 16px;
  border-radius: 6px;
  background-color: #f0f4ff;
  color: #003366;
  text-decoration: none;
  font-weight: bold;
  border: 1px solid #c2d4f5;
  transition: background-color 0.3s, color 0.3s;
  font-size: 14px;
}

.role-links a:hover {
  background-color: #dce8ff;
  color: #002855;
}

.forgot-password {
  margin-top: 10px;
}

.forgot-password a {
  font-size: 14px;
  color: #0055a5;
  text-decoration: underline;
}
</style> -->
