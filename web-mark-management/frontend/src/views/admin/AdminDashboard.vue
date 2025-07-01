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
        <li @click="$router.push('/admin/logs')">📜 View Logs</li>
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
          <span>Welcome, {{ user.name }} ({{ user.role }})</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <!-- Dashboard 内容 -->
      <main class="dashboard">
        <h1>Admin Dashboard</h1>
        <p style="text-align:center">Select a function from the left menu.</p>
      </main>
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

<style scoped>
/* 布局 */
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

/* Dashboard 内容区域 */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #1e3a8a;
  text-align: center;
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
