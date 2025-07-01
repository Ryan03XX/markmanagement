<template>
  <div class="app-layout">
    <!-- Sidebar -->
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

    <!-- Main content -->
    <div class="main-content">
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ user.name }} ({{ user.role }})</span>
        </div>
      </header>

      <main class="dashboard">
        <h1>🔍 Student Result Check</h1>

        <div class="search-section">
          <input
            type="text"
            v-model="searchId"
            placeholder="Enter Student Matric Number..."
          />
          <button @click="searchStudent">Search</button>
        </div>

        <div v-if="courses.length > 0" class="report-section">
          <button @click="generateStudentReport">
            📄 Generate Student Report
          </button>
        </div>

        <div v-if="courses.length > 0" class="results-section">
          <h2>📚 Enrolled Courses & Results</h2>
          <div v-for="course in courses" :key="course.id" class="course-card">
            <h3>{{ course.code }} - {{ course.name }}</h3>
            <p><strong>Instructor:</strong> {{ course.instructor || "N/A" }}</p>

            <div v-if="course.result">
              <p>
                <strong>Final Exam Mark:</strong>
                {{ course.result.final_exam_mark ?? "N/A" }}
              </p>
              <p>
                <strong>Final Mark:</strong>
                {{ course.result.final_mark ?? "N/A" }}
              </p>
              <div class="component-marks">
                <p><strong>Component Marks:</strong></p>
                <ul>
                  <li
                    v-for="comp in course.result.component_results"
                    :key="comp.component_name"
                  >
                    {{ comp.component_name }}: {{ comp.mark }}
                  </li>
                </ul>
              </div>
            </div>

            <div v-else>
              <p style="color: gray">No results available for this course.</p>
            </div>
          </div>
        </div>

        <div v-if="searched && courses.length === 0">
          <p style="text-align: center; color: red">
            No enrolled courses found for this student.
          </p>
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
      searchId: "",
      courses: [],
      searched: false,
    };
  },
  created() {
    const savedUser = JSON.parse(localStorage.getItem("user"));
    if (!savedUser || savedUser.role !== "advisor") {
      this.$router.push("/advisor/login");
    } else {
      this.user = savedUser;
    }
  },
  methods: {
    logout() {
      localStorage.removeItem("user");
      this.$router.push("/advisor/login");
    },
    async searchStudent() {
      this.searched = false;
      this.courses = [];

      if (!this.searchId) {
        alert("Please enter a student ID.");
        return;
      }

      try {
        // ✅ 使用正确的 matric_no 路由
        const res = await fetch(
          `http://localhost:8080/api/student/matric/${this.searchId}/enrolled-courses`
        );
        const data = await res.json();

        if (data.success) {
          this.courses = data.courses;
          for (let course of this.courses) {
            // ✅ 使用 by-matric-no 路由
            const resultRes = await fetch(
              `http://localhost:8080/api/student/by-matric-no/${this.searchId}/course/${course.id}/result`
            );
            const resultData = await resultRes.json();

            if (resultData.success) {
              // 兼容旧代码字段结构
              course.result = {
                final_exam_mark:
                  resultData.final_result?.final_exam_mark ?? null,
                final_mark: resultData.final_result?.final_mark ?? null,
                component_results: resultData.component_results || [],
              };
            } else {
              course.result = null;
            }
          }
        }

        this.searched = true;
      } catch (err) {
        console.error(err);
        alert("Failed to fetch data.");
      }
    },
    generateStudentReport() {
      if (!this.courses.length) return;

      let csv =
        "No,Course Code,Course Name,Final Exam Mark,Final Mark,Component Marks\n";

      this.courses.forEach((course, index) => {
        const compStr =
          course.result?.component_results
            ?.map((comp) => `${comp.component_name}: ${comp.mark}`)
            .join(" | ") || "N/A";
        csv += `${index + 1},${course.code},${course.name},${
          course.result?.final_exam_mark ?? "N/A"
        },${course.result?.final_mark ?? "N/A"},"${compStr}"\n`;
      });

      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `${this.searchId}_Student_Report.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  },
};
</script>

<style scoped>
/* Layout */
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
  margin: 0;
}

/* Sidebar */
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

/* Content */
.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* Navbar */
.navbar {
  background-color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Main area */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #1e3a8a;
  text-align: center;
}

.search-section {
  text-align: center;
  margin-bottom: 2rem;
}

.search-section input {
  padding: 0.6rem;
  width: 250px;
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-right: 1rem;
}

.search-section button {
  padding: 0.6rem 1.2rem;
  background-color: #1e3a8a;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.search-section button:hover {
  background-color: #3749a3;
}

/* Course card */
.results-section {
  max-width: 800px;
  margin: auto;
}

.course-card {
  background-color: white;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.course-card h3 {
  margin-bottom: 0.5rem;
  color: #1e3a8a;
}

.component-marks ul {
  padding-left: 1rem;
  margin-top: 0.5rem;
}

.component-marks li {
  list-style: disc;
  margin-bottom: 0.3rem;
}

/* Logout */
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

.report-section {
    text-align: center;
    margin-top: 1rem;
}

.report-section button {
    padding: 0.6rem 1.2rem;
    background-color: #10b981;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.report-section button:hover {
    background-color: #059669;
}
</style>
