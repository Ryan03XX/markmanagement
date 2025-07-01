<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">🎓 Advisor Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/advisor/ranking')">📊 Ranking View</li>
        <li @click="$router.push('/advisor/students')">🧑‍🎓 Student Check</li>
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
        </div>
      </header>

      <!-- 页面主要内容 -->
      <main class="dashboard">
        <h1>📊 Course Ranking View</h1>

        <div class="course-selector">
          <label for="course">Select Course:</label>
          <select v-model="selectedCourseId" @change="fetchRanking">
            <option disabled value="">-- Choose a course --</option>
            <option
              v-for="course in courses"
              :key="course.id"
              :value="course.id"
            >
              {{ course.name }} - {{ course.lecturer_name }}
            </option>
          </select>
        </div>

        <div v-if="ranking.length">
          <h3>Ranking for {{ courseName }}</h3>
          <table class="ranking-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Matric No</th>
                <th>Name</th>
                <th>Final Mark</th>
                <th>Components</th>
                <th>Feedback</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(student, index) in ranking"
                :key="student.student_id"
                :class="{
                  'at-risk': atRiskStudents.includes(student.student_id),
                }"
              >
                <td>{{ index + 1 }}</td>
                <td>{{ student.matric_no }}</td>
                <td>{{ student.name }}</td>
                <td>{{ student.final_mark }}</td>
                <td>
                  <ul>
                    <li v-for="comp in student.components" :key="comp.name">
                      {{ comp.name }}: {{ comp.mark }}
                    </li>
                  </ul>
                </td>
                <td>{{ student.feedback }}</td>
                <td>
                  <button @click="flagStudent(student.student_id)">
                    {{
                      atRiskStudents.includes(student.student_id)
                        ? "✅ Unflag"
                        : "🔴 Flag"
                    }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-else-if="selectedCourseId">No data found.</p>
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
      selectedCourseId: "",
      ranking: [],
      courseName: "",
      atRiskStudents: JSON.parse(
        localStorage.getItem("flaggedStudents") || "[]"
      ),
    };
  },
  created() {
    const savedUser = JSON.parse(localStorage.getItem("user"));
    if (!savedUser || savedUser.role !== "advisor") {
      this.$router.push("/advisor/login");
    } else {
      this.user = savedUser;
      this.fetchCourses();
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch("http://localhost:8080/api/courses");
        const data = await res.json();
        this.courses = data;
      } catch (err) {
        console.error("Failed to load courses", err);
      }
    },
    async fetchRanking() {
      if (!this.selectedCourseId) return;

      try {
        const res = await fetch(
          `http://localhost:8080/api/student/course-ranking/${this.selectedCourseId}`
        );
        const data = await res.json();
        if (data.success) {
          this.courseName = data.course_name;
          this.ranking = data.ranking;

          // 确保 flag 状态维持一致
          const flagged = JSON.parse(
            localStorage.getItem("flaggedStudents") || "[]"
          );
          this.atRiskStudents = flagged;
        }
      } catch (err) {
        console.error("Failed to fetch ranking", err);
      }
    },
    logout() {
      localStorage.removeItem("user");
      this.$router.push("/advisor/login");
    },
    async flagStudent(studentId) {
      const isFlagged = this.atRiskStudents.includes(studentId);

      if (isFlagged) {
        // 取消 flag
        this.atRiskStudents = this.atRiskStudents.filter(
          (id) => id !== studentId
        );
      } else {
        // 添加 flag
        this.atRiskStudents.push(studentId);

        // 可选：发送通知
        try {
          const res = await fetch(
            `http://localhost:8080/api/student/${studentId}/notify`,
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                message:
                  "You are flagged as at-risk. Please meet your advisor.",
              }),
            }
          );
          const data = await res.json();
          if (!data.success) {
            alert("Failed to notify student");
          }
        } catch (err) {
          console.error("Error sending notification", err);
        }
      }

      // 更新 localStorage
      localStorage.setItem(
        "flaggedStudents",
        JSON.stringify(this.atRiskStudents)
      );
    },
  },
};
</script>

<style scoped>
/* ----------- 布局样式 ----------- */
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
  margin: 0;
}

/* 侧边栏 */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 94vh; /* 固定高度 */
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  overflow: hidden; /* 防止自己滚动 */
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

/* Dashboard 区域 */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #1e3a8a;
  text-align: center;
}

/* course selector */
.course-selector {
  margin-bottom: 1.5rem;
}

select {
  padding: 0.4rem;
  font-size: 1rem;
}

/* 表格 */
.ranking-table {
  width: 100%;
  border-collapse: collapse;
}

.ranking-table th,
.ranking-table td {
  border: 1px solid #ccc;
  padding: 0.5rem;
  text-align: left;
}

.ranking-table th {
  background-color: #f0f0f0;
}

/* 登出按钮 */
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

.at-risk {
  background-color: #fee2e2 !important; /* 淡红色背景 */
}
</style>
