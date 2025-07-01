<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">📘 Lecturer Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/lecturer/dashboard')">📚 My Courses</li>
        <li @click="$router.push('/lecturer/remark-requests')">📝 Remark Requests</li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- 主内容区域 -->
    <div class="main-content">
      <!-- 顶部导航 -->
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, Lecturer</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <!-- Dashboard 内容 -->
      <main class="dashboard">
        <h1>My Courses</h1>

        <div v-if="courses.length > 0" class="course-list">
          <div v-for="course in courses" :key="course.id" class="course-card">
            <p class="course-title">{{ course.code }} - {{ course.name }}</p>
            <div class="button-group">
              <router-link
                :to="`/lecturer/course/${course.id}`"
                class="btn green"
              >📝 Grade Students</router-link>
              <router-link
                :to="`/lecturer/course-detail/${course.id}`"
                class="btn blue"
              >➕ Add Assessment</router-link>
              <router-link
                :to="`/lecturer/course-ranking/${course.id}`"
                class="btn orange"
              >🏆 View Ranking</router-link>
            </div>
          </div>
        </div>

        <p v-else class="no-course">No courses found.</p>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      courses: []
    }
  },
  async mounted() {
    const role = localStorage.getItem('role');
    if (role !== 'lecturer') {
      this.$router.push('/lecturer/login');
      return;
    }

    const lecturerId = localStorage.getItem('user_id');
    const res = await fetch(`http://localhost:8080/api/lecturer/courses/${lecturerId}`);
    const data = await res.json();
    if (data.success) {
      this.courses = data.courses;
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('role');
      localStorage.removeItem('user_id');
      this.$router.push('/lecturer/login');
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
  margin: 0;
}

/* 侧边栏 */
.sidebar {
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
}

.logo {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 2rem;
  text-align: center;
}

.nav-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-links li {
  padding: 0.8rem 1rem;
  margin-bottom: 0.5rem;
  background-color: #3749a3;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
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
  border: none;
  color: white;
  padding: 0.4rem 0.8rem;
  margin-left: 1rem;
  border-radius: 5px;
  cursor: pointer;
}

.navbar button:hover {
  background-color: #dc2626;
}

/* Dashboard 区域 */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 1.8rem;
  color: #1e3a8a;
  margin-bottom: 1rem;
  text-align: center;
}

/* Course Cards */
.course-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.course-card {
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 1rem;
}

.course-title {
  font-weight: bold;
  font-size: 1.1rem;
  margin-bottom: 0.8rem;
  color: #1e3a8a;
}

.button-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  text-decoration: none;
  color: white;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn.green {
  background-color: #28a745;
}

.btn.green:hover {
  background-color: #218838;
}

.btn.blue {
  background-color: #007bff;
}

.btn.blue:hover {
  background-color: #0056b3;
}

.btn.orange {
  background-color: #fd7e14;
}

.btn.orange:hover {
  background-color: #e8590c;
}

.no-course {
  text-align: center;
  color: #888;
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
