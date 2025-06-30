<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">🎓 Admin Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/admin/dashboard')">👥Dashboard</li>
        <li @click="$router.push('/admin/courses/add')">📚 Manage Courses</li>
        <li @click="$router.push('/admin/courses/assign')">👨‍🏫 Assign Lecturers</li>
        <li @click="$router.push('/admin/users/add')">🎓 Add Users</li>
        <li @click="$router.push('/admin/users/manage')">👥 Manage Users</li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- 主内容 -->
    <div class="main-content">
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ user.name }} ({{ user.role }})    </span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <main class="dashboard">
        <!-- <button class="back-button" @click="goBack">← Back to Dashboard</button> -->

        <h2>Assign Course to Lecturer</h2>

        <div class="form-section">
          <div class="form-group">
            <label>Course:</label>
            <select v-model="selectedCourseId" required>
              <option v-for="c in courses" :value="c.id" :key="c.id">
                {{ c.code }} - {{ c.name }} ({{ c.lecturer_name || 'Unassigned' }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Lecturer:</label>
            <select v-model="selectedLecturerId" required>
              <option v-for="l in lecturers" :value="l.id" :key="l.id">
                {{ l.name }}
              </option>
            </select>
          </div>

          <button class="assign-btn" @click="assign">Assign</button>
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
      courses: [],
      lecturers: [],
      selectedCourseId: null,
      selectedLecturerId: null,
      message: ''
    }
  },
  async created() {
    const savedUser = JSON.parse(localStorage.getItem('user'))
    if (!savedUser || savedUser.role !== 'admin') {
      this.$router.push('/admin/login')
    } else {
      this.user = savedUser
    }

    const courseRes = await fetch('http://localhost:8080/api/courses');
    this.courses = await courseRes.json();

    const lecturerRes = await fetch('http://localhost:8080/api/lecturers');
    this.lecturers = await lecturerRes.json();
  },
  methods: {
    goBack() {
      this.$router.push('/admin/dashboard')
    },
    logout() {
      localStorage.removeItem('user')
      this.$router.push('/admin/login')
    },
    async assign() {
      const res = await fetch('http://localhost:8080/api/assign-course', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          course_id: this.selectedCourseId,
          lecturer_id: this.selectedLecturerId
        })
      })

      const result = await res.json()

      if (result.success) {
        this.message = result.message
        this.selectedCourseId = null
        this.selectedLecturerId = null
        await this.fetchCourses()
      } else {
        this.message = result.message || 'Failed to assign course.'
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
.dashboard h2,
.dashboard h3 {
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
select {
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
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
  color: green;
  margin-top: 10px;
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
  padding-top: 1rem;
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
