<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">🎓 Admin Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/admin/dashboard')">👥Dashboard</li>
        <li @click="$router.push('/admin/courses/add')">📚 Manage Courses</li>
        <li @click="$router.push('/admin/courses/assign')">👨‍🏫 Assign Lecturers</li>
        <li @click="$router.push('/admin/users/add')">🎓 Add Users</li>
        <li @click="$router.push('/admin/users/manage')">👥 Manage Users</li>
        <li @click="$router.push('/admin/logs')">📜 View Logs</li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ user.name }} ({{ user.role }})    </span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <main class="dashboard">
        <h2>Add New User</h2>
        <div class="form-section">
          <div class="form-group">
            <label>Role:</label>
            <select v-model="role" required>
              <option disabled value="">-- Select Role --</option>
              <option value="admin">Admin</option>
              <option value="advisor">Advisor</option>
              <option value="lecturer">Lecturer</option>
              <option value="student">Student</option>
            </select>
          </div>

          <div class="form-group" v-if="role">
            <label>Name:</label>
            <input type="text" v-model="name" required />
          </div>

          <div class="form-group" v-if="role === 'admin' || role === 'lecturer' || role === 'advisor'">
            <label>Email:</label>
            <input type="email" v-model="email" required />
          </div>

          <div class="form-group" v-if="role === 'student'">
            <label>Matric Number:</label>
            <input type="text" v-model="matricNo" required />
          </div>

          <div class="form-group" v-if="role">
            <label>Password:</label>
            <input type="password" v-model="password" required />
          </div>

          <button class="assign-btn" @click="register">Register</button>
          <p v-if="message" :class="{ message: true, success: success, error: !success }">{{ message }}</p>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: {},
      name: '',
      email: '',
      matricNo: '',
      password: '',
      role: '',
      message: '',
      success: false
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
    },
    async register() {
      const token = localStorage.getItem('token')
      let payload = {
        name: this.name,
        password: this.password,
        role: this.role
      }

      if (this.role === 'admin' || this.role === 'lecturer' || this.role === 'advisor') {
        payload.email = this.email
      } else if (this.role === 'student') {
        payload.matric_no = this.matricNo
      }

      try {
        const res = await fetch('http://localhost:8080/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify(payload)
        })

        const result = await res.json()
        this.message = result.message
        this.success = result.success

        if (result.success) {
          this.name = ''
          this.email = ''
          this.matricNo = ''
          this.password = ''
          this.role = ''
        }
      } catch (err) {
        this.message = 'Network error'
        this.success = false
        console.error(err)
      }
    }
  }
}
</script>

<style scoped>
/* 整体布局 */
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
}

/* 侧边栏 */
.sidebar {
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
}
.logo {
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 2rem;
}
.nav-links {
  list-style: none;
  padding: 0;
}
.nav-links li {
  padding: 0.8rem 1rem;
  background-color: #3749a3;
  margin-bottom: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}
.nav-links li:hover {
  background-color: #475aca;
}

/* 主内容 */
.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* 顶部导航栏 */
.navbar {
  background-color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.navbar button {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 5px;
  cursor: pointer;
}
.navbar button:hover {
  background-color: #dc2626;
}

/* 内容区 */
.dashboard {
  padding: 2rem;
}
.dashboard h2 {
  text-align: center;
  margin-bottom: 20px;
}

.form-section {
  max-width: 600px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
}
input, select {
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.assign-btn {
  background-color: #007bff;
  color: white;
  padding: 10px;
  font-size: 16px;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}
.assign-btn:hover {
  background-color: #0056b3;
}
.message {
  text-align: center;
  margin-top: 10px;
}
.success {
  color: green;
}
.error {
  color: red;
}

.logout-section {
  margin-top: auto;
  padding-top: 17rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.logout-section button {
  width: 100%;
  padding: 0.6rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.logout-section button:hover {
  background-color: #dc2626;
}
</style>
