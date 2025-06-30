<template>
  <div class="login-page">
    <div class="login-card">
      <h2>Set New Password</h2>
      <form @submit.prevent="resetPassword">
        <input v-model="newPassword" type="password" placeholder="New Password" required />
        <button type="submit">Reset Password</button>
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
      newPassword: '',
      message: '',
      error: ''
    }
  },
  mounted() {
    this.token = this.$route.query.token;
  },
  methods: {
    async resetPassword() {
      try {
        const res = await axios.post('http://localhost:8080/api/reset-password', {
          token: this.token,
          password: this.newPassword
        });
        if (res.data.success) {
          this.message = 'Password successfully reset. You can now log in.';
          this.error = '';
        } else {
          this.error = res.data.message;
        }
      } catch (err) {
        this.error = 'Reset failed.';
        console.error(err);
      }
    }
  }
}
</script>
