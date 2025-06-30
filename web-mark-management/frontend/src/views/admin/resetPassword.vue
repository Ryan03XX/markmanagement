<template>
  <div class="p-4">
    <h2 class="text-xl mb-4">Update User Password</h2>
    <form @submit.prevent="updatePassword">
      <div class="mb-2">
        <label>User ID:</label>
        <input v-model="userId" class="border px-2 py-1" />
      </div>
      <div class="mb-2">
        <label>New Password:</label>
        <input type="password" v-model="password" class="border px-2 py-1" />
      </div>
      <button class="bg-blue-500 text-white px-4 py-1">Update</button>
    </form>
    <p v-if="message" class="mt-2">{{ message }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      userId: '',
      password: '',
      message: ''
    };
  },
  methods: {
    async updatePassword() {
      try {
        const res = await fetch(`http://localhost:8080/api/users/${this.userId}/password`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          },
          body: JSON.stringify({ password: this.password })
        });
        const data = await res.json();
        this.message = data.message;
      } catch (error) {
        this.message = 'Error updating password';
      }
    }
  }
};
</script>
