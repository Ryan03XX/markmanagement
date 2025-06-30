<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">📘 Lecturer Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/lecturer/dashboard')">📚 My Courses</li>
        <li @click="$router.push('/lecturer/remark-requests')">
          📝 Remark Requests
        </li>
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

      <!-- 添加评估项目 -->
      <main class="dashboard">
        <!-- <div class="header-bar">
          <h1 class="title">📝 Add Assessment Components</h1>
          <button @click="goBack" class="btn red">🔙 Back</button>
        </div> -->
        <div class="header-bar">
          <h1 class="title">📝 Add Assessment Components</h1>
          <button @click="goBack" class="btn red back-button">🔙 Back</button>
        </div>

        <div class="course-title">{{ course.code }} - {{ course.name }}</div>
        <h2 class="text-lg font-semibold mb-2">
          Assessment Components ({{ totalWeight }}% of 100%)
        </h2>
        <form @submit.prevent="addComponent" class="add-form">
          <input
            v-model="newComponent.name"
            placeholder="Component Name"
            class="input"
            required
          />
          <input
            v-model.number="newComponent.weight"
            type="number"
            placeholder="Weight %"
            class="input"
            required
          />
          <button class="btn green">Add</button>
        </form>

        <p v-if="totalWeight > 100" style="color: red">
          ⚠️ Total weight exceeds 100%.
        </p>

        <div v-if="components.length > 0" class="component-list">
          <div v-for="comp in components" :key="comp.id" class="course-card">
            <p class="course-title2">{{ comp.name }} – {{ comp.weight }}%</p>
          </div>
        </div>

        <p v-else class="no-course">No components added yet.</p>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      course: {},
      components: [],
      newComponent: { name: "", weight: "" },
    };
  },
  computed: {
    totalWeight() {
      return this.components.reduce(
        (sum, c) => sum + parseFloat(c.weight || 0),
        0
      );
    },
  },
  async mounted() {
    const role = localStorage.getItem("role");
    if (role !== "lecturer") {
      this.$router.push("/lecturer/login");
      return;
    }

    const id = this.$route.params.id;

    try {
      const [courseRes, compRes] = await Promise.all([
        fetch(`http://localhost:8080/api/course/${id}`),
        fetch(`http://localhost:8080/api/course/${id}/components`),
      ]);

      const courseData = await courseRes.json();
      const compData = await compRes.json();

      if (courseData.success) this.course = courseData.course;
      if (compData.success) this.components = compData.components;
    } catch (err) {
      console.error("Failed to fetch course or components:", err);
    }
  },
  methods: {
    async addComponent() {
      const id = this.$route.params.id;
      if (!this.newComponent.name || this.newComponent.weight === "") return;

      try {
        const res = await fetch(
          `http://localhost:8080/api/course/${id}/components`,
          {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(this.newComponent),
          }
        );
        const data = await res.json();
        if (data.success) {
          this.components.push(data.component);
          this.newComponent.name = "";
          this.newComponent.weight = "";
        } else {
          alert(data.message || "Add failed.");
        }
      } catch (err) {
        console.error("Error adding component:", err);
      }
    },
    logout() {
      localStorage.clear();
      this.$router.push("/lecturer/login");
    },
    goBack() {
      this.$router.push('/lecturer/dashboard');
    },
  },
};
</script>

<style scoped>
/* 公共布局 */
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
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
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

/* Dashboard 内容 */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 1.8rem;
  color: #1e3a8a;
  margin-bottom: 1rem;
  text-align: center;
}

.course-title {
  font-weight: bold;
  font-size: 1.5rem;
  margin-bottom: 0.8rem;
  color: #1e3a8a;
}

.course-title2 {
  font-weight: bold;
  font-size: 1.1rem;
  margin-bottom: 0.8rem;
  color: #1e3a8a;
}

/* 表单区域 */
.add-form {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 1.5rem;
  align-items: center;
  justify-content: center;
}

.input {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  min-width: 180px;
  font-size: 0.95rem;
}

/* 组件列表样式 */
.component-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.course-card {
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 1rem;
}

/* 按钮样式 */
.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  text-decoration: none;
  color: white;
  font-size: 0.9rem;
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
}

.btn.green {
  background-color: #28a745;
}

.btn.green:hover {
  background-color: #218838;
}

.no-course {
  text-align: center;
  color: #888;
}

.header-bar {
  position: relative;
  display: flex;
  justify-content: center;
  padding: 1rem 2rem;
  border-bottom: 1px solid #ccc;
}

.title {
  font-size: 1.5rem;
  margin: 0;
}

.back-button {
  position: absolute;
  top: 1rem;
  right: 0; /* 顶到最右边 */
}

.btn.red {
  background-color: #dc3545;
}

.btn.green {
  background-color: #28a745;
}

.btn.green:hover {
  background-color: #218838;
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
