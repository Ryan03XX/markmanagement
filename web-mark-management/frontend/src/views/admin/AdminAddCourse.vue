<template>
  <div class="app-layout">
    <!-- 固定侧边栏 -->
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

    <!-- 主内容区 -->
    <div class="main-content">
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ user.name }} ({{ user.role }}) </span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <main class="dashboard">
        <!-- <button class="back-button" @click="goBack">← Back to Dashboard</button> -->

        <h2>Add New Course</h2>

        <form @submit.prevent="submitCourse" class="course-form">
          <input v-model="code" type="text" placeholder="Course Code" required />
          <input v-model="name" type="text" placeholder="Course Name" required />
          <button type="submit">Add Course</button>
        </form>

        <p v-if="message" class="message">{{ message }}</p>

        <h3>Course List</h3>
        <table class="course-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Lecturer</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="course in courses" :key="course.id">
              <td>{{ course.code }}</td>
              <td>{{ course.name }}</td>
              <td>{{ course.lecturer_name || 'Unassigned' }}</td>
            </tr>
          </tbody>
        </table>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      user: {},
      code: '',
      name: '',
      message: '',
      courses: []
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
  mounted() {
    this.fetchCourses()
  },
  methods: {
    goBack() {
      this.$router.push('/admin/dashboard')
    },
    logout() {
      localStorage.removeItem('user')
      this.$router.push('/admin/login')
    },
    async submitCourse() {
      try {
        const res = await fetch('http://localhost:8080/api/courses', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: this.code, name: this.name })
        })

        if (!res.ok) {
          const errorText = await res.text()
          throw new Error(`Server Error: ${res.status} - ${errorText}`)
        }

        const result = await res.json()

        if (result.success) {
          this.message = result.message
          this.code = ''
          this.name = ''
          await this.fetchCourses()
        } else {
          this.message = result.message || 'Unknown error'
        }
      } catch (error) {
        console.error('Error adding course:', error)
        this.message = 'Error: ' + error.message
      }
    },
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:8080/api/courses')
        const data = await res.json()
        this.courses = data
      } catch (error) {
        console.error('Failed to fetch courses:', error)
      }
    }
  }
}
</script>

<style scoped>
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
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

.dashboard h2,
.dashboard h3 {
  text-align: center;
  margin-bottom: 20px;
}

.course-form {
  max-width: 400px;
  margin: 0 auto 30px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.course-form input,
.course-form button {
  padding: 10px;
  font-size: 16px;
}

.course-form button {
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
}

.course-form button:hover {
  background-color: #0056b3;
}

.message {
  text-align: center;
  color: green;
  margin-bottom: 20px;
}

.course-table {
  width: 100%;
  border-collapse: collapse;
}

.course-table th,
.course-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}

.course-table th {
  background-color: #f0f0f0;
}

.back-button {
  margin-bottom: 10px;
  background-color: #ccc;
  color: #333;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.back-button:hover {
  background-color: #bbb;
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
