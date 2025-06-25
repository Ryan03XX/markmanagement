<template>
  <div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded p-6">
      <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

      <p class="mb-4">Welcome, {{ user.name }} ({{ user.role }})</p>

      <div class="space-y-4">
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full text-left"
                @click="$router.push('/admin/courses/add')">
          📚 Manage Courses
        </button>
        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full text-left"
                @click="$router.push('/admin/courses/assign')">
          👨‍🏫 Manage Lecturers
        </button>
        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 w-full text-left"
                @click="$router.push('/admin/users/add')">
          🎓 Manage Users
        </button>
        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 w-full text-left"
                @click="logout">
          🚪 Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: {}
    }
  },
  created() {
    const savedUser = JSON.parse(localStorage.getItem('user'))
    if (!savedUser || savedUser.role !== 'admin') {
      this.$router.push('/admin/login')
    } else {
      this.user = savedUser
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('user')
      this.$router.push('/admin/login')
    }
  }
}
</script>
