<template>
  <div class="login-page">
    <div class="login-card">
      <img src="@/assets/utm-logo.png" alt="School Logo" class="logo" />
      <h2>Admin Login</h2>
      <form @submit.prevent="login">
        <input v-model="email" type="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Password" required />
        <button type="submit">Login</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
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
        if (result.success && result.user.role === 'admin') {
          localStorage.setItem('user', JSON.stringify(result.user));
          this.$router.push('/admin/dashboard');
        } else {
          this.error = 'Invalid credentials or not an admin';
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
</style>
