<template>
  <div class="login-page">
    <div class="login-card">
      <h2>Reset Password</h2>
      <form @submit.prevent="submitEmail">
        <input v-model="email" type="email" placeholder="Enter your email" required />
        <button type="submit">Send Reset Link</button>
        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      email: '',
      message: '',
      error: ''
    }
  },
  methods: {
    async submitEmail() {
      try {
        const res = await axios.post('http://localhost:8080/api/request-password-reset', {
          email: this.email
        });
        if (res.data.success) {
          this.message = res.data.message;
          this.error = '';
        } else {
          this.error = res.data.message;
          this.message = '';
        }
      } catch (err) {
        this.error = 'Something went wrong.';
        console.error(err);
      }
    }
  }
}
</script>

<style scoped>
.success {
  color: green;
  margin-top: 10px;
}
</style>
