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

    <!-- 主区域 -->
    <div class="main-content">
      <!-- 顶部导航 -->
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, Lecturer</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <!-- 主体内容 -->
      <main class="dashboard">
        <div class="header-bar">
          <h1 class="title">🏆 Course Ranking</h1>
          <button @click="goBack" class="btn red back-button">🔙 Back</button>
        </div>
        <p class="mb-2 text-gray-600 text-center">Course: {{ courseName }}</p>

        <div class="export-container">
          <button @click="exportCSV" class="export-btn">⬇️ Export as CSV</button>
          <button @click="showChartModal = true" class="export-btn" style="margin-left: 10px;">
            📊 Show Graph
          </button>
        </div>

        <table class="ranking-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Student Name</th>
              <th>Matric No</th>
              <th v-for="(name, index) in componentNames" :key="index">
                {{ name }}
              </th>
              <th>Total Score</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(student, index) in rankings"
              :key="student.id"
              :class="{ 'at-risk-row': isAtRisk(student.final_mark) }"
            >
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.matric_no }}</td>
              <td v-for="name in componentNames" :key="name">
                {{
                  student.components.find((c) => c.name === name)?.mark ?? "-"
                }}
              </td>
              <td>{{ student.final_mark }}</td>
            </tr>
          </tbody>
        </table>

        <!-- 图表 Modal -->
        <div v-if="showChartModal" class="modal-overlay" @click.self="showChartModal = false">
          <div class="modal-content">
            <button class="modal-close" @click="showChartModal = false">✖</button>
            <FinalMarkChart :students="rankings" />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import FinalMarkChart from "@/components/FinalMarkChart.vue"; // 修改路径根据实际情况

export default {
  components: {
    FinalMarkChart,
  },
  data() {
    return {
      courseId: null,
      courseName: "",
      rankings: [],
      componentNames: [],
      showChartModal: false,
    };
  },
  async mounted() {
    this.courseId = this.$route.params.id;
    const token = localStorage.getItem("token");
    if (!token || localStorage.getItem("role") !== "lecturer") {
      this.$router.push("/lecturer/login");
      return;
    }

    try {
      const res = await fetch(
        `http://localhost:8080/api/lecturer/course-ranking/${this.courseId}`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      const data = await res.json();
      if (data.success) {
        this.rankings = data.ranking;
        this.courseName = data.course_name || "Course";

        // 提取组件名
        const names = new Set();
        this.rankings.forEach((s) => {
          s.components?.forEach((c) => names.add(c.name));
        });
        this.componentNames = Array.from(names);
      }
    } catch (error) {
      console.error("Failed to fetch rankings:", error);
    }
  },
  methods: {
    exportCSV() {
      let header = [
        "Rank",
        "Name",
        "Matric No",
        ...this.componentNames,
        "Final Mark",
      ];
      let csv = header.join(",") + "\n";

      this.rankings.forEach((student, index) => {
        const row = [index + 1, `"${student.name}"`, `"${student.matric_no}"`];

        this.componentNames.forEach((name) => {
          const comp = student.components?.find((c) => c.name === name);
          row.push(comp?.mark ?? "");
        });

        row.push(student.final_mark);
        csv += row.join(",") + "\n";
      });

      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.setAttribute("download", `${this.courseName}_ranking.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    isAtRisk(mark) {
      return Number(mark) < 40;
    },
    goBack() {
      this.$router.push("/lecturer/dashboard");
    },
    logout() {
      localStorage.clear();
      this.$router.push("/lecturer/login");
    },
  },
};
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
}

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

.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

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

.dashboard {
  padding: 2rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.9rem;
  color: white;
  border: none;
  cursor: pointer;
}

.btn.blue {
  background-color: #2563eb;
}

.btn.blue:hover {
  background-color: #1d4ed8;
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
  right: 0;
}

.export-container {
  text-align: right;
  margin-bottom: 1rem;
}

.export-btn {
  background-color: #28a745;
  color: white;
  padding: 8px 16px;
  border: none;
  font-size: 14px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.export-btn:hover {
  background-color: #218838;
}

.ranking-table {
  width: 100%;
  border-collapse: collapse;
  background-color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  font-size: 14px;
}

.ranking-table thead {
  background-color: #f3f4f6;
}

.ranking-table th,
.ranking-table td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}

.ranking-table tbody tr:nth-child(even) {
  background-color: #f9fafb;
}

.at-risk-row {
  background-color: #fee2e2 !important;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 90%;
  max-height: 90%;
  overflow-y: auto;
  position: relative;
}

.modal-close {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #dc2626;
  color: white;
  border: none;
  padding: 4px 8px;
  font-size: 1rem;
  border-radius: 4px;
  cursor: pointer;
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
