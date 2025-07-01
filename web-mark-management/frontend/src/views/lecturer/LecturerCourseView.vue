<template>
  <div class="app-layout">
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <div class="main-content">
      <!-- Navbar -->
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, Lecturer</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <!-- Page Content -->
      <main class="dashboard">
        <!-- <div class="header-bar">
          <h1 class="title">📝 Grade Students</h1>
          <button @click="goBack" class="btn red">🔙 Back</button>
        </div> -->

        <div class="header-bar">
          <h1 class="title">📝 Grade Students</h1>
          <button @click="goBack" class="btn red back-button">🔙 Back</button>
        </div>

        <!-- <div class="import-box">
          <label class="import-label" for="fileInput">📥 Import Scores via Excel:</label>
          <button @click="downloadExample">Download Template</button>
          <input type="file" id="fileInput" accept=".xlsx, .xls, .csv" @change="handleFileUpload" class="file-input" />
        </div> -->
        <h2 class="subtitle">Students in Course: {{ courseName }}</h2>

        <table class="grade-table">
          <thead>
            <tr>
              <th>Matric No</th>
              <th>Name</th>
              <th v-for="component in components" :key="component.id">
                {{ component.name }} ({{ component.weight }}%)
              </th>
              <th>Final Exam (30%)</th>
              <th>Final Mark</th>
              <th>Feedback</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.student_id">
              <td>{{ student.matric_no }}</td>
              <td>{{ student.name }}</td>
              <td v-for="component in components" :key="component.id">
                <input type="number" v-model.number="student.scores[component.id]" class="input-field" />
              </td>
              <td>
                <input type="number" v-model.number="student.final_exam_mark" class="input-field" />
              </td>
              <td class="text-center">{{ calculateFinal(student) }}</td>
              <td>
                <textarea v-model="student.feedback" class="input-field h-16 resize-none w-full"></textarea>
              </td>
              <td class="text-center">
                <button class="btn green" @click="saveGrade(student)">💾 Save</button>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="message" class="message">{{ message }}</p>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      components: [],
      students: [],
      message: '',
      courseName: ''
    };
  },
  async created() {
    const courseId = this.$route.params.id;
    await this.fetchCourseName(courseId);
    await this.fetchComponents(courseId);
    await this.fetchStudents(courseId);
  },
  methods: {
    async fetchCourseName(courseId) {
      const res = await fetch(`http://localhost:8080/api/course/${courseId}`);
      const data = await res.json();
      if (data.success) {
        this.courseName = data.course.name;
      }
    },
    async fetchComponents(courseId) {
      const res = await fetch(`http://localhost:8080/api/course/${courseId}/components`);
      const data = await res.json();
      this.components = data.components;
    },
    async fetchStudents(courseId) {
      const res = await fetch(`http://localhost:8080/api/lecturer/course/${courseId}/students`);
      const data = await res.json();
      this.students = data.students.map(student => {
        const scores = {};
        (student.scores || []).forEach(s => {
          scores[s.component_id] = s.mark;
        });
        return {
          ...student,
          scores,
          final_exam_mark: student.final_exam_mark || 0,
          feedback: student.feedback || ''
        };
      });
    },
    calculateFinal(student) {
      let componentTotal = 0;
      this.components.forEach(c => {
        const score = parseFloat(student.scores[c.id]) || 0;
        componentTotal += score * (c.weight / 100);
      });
      const componentWeighted = componentTotal * 0.7;
      const finalWeighted = (parseFloat(student.final_exam_mark) || 0) * 0.3;
      return (componentWeighted + finalWeighted).toFixed(2);
    },
    async saveGrade(student) {
      const token = localStorage.getItem('token');
      const component_scores = {};
      for (const id in student.scores) {
        component_scores[parseInt(id)] = student.scores[id];
      }

      const res = await fetch('http://localhost:8080/api/lecturer/grade', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          course_id: this.$route.params.id,
          student_id: student.id,
          component_scores,
          final_exam_mark: student.final_exam_mark,
          feedback: student.feedback
        })
      });

      const data = await res.json();
      this.message = `Saved ${student.name}: ${data.message}`;
    },
    goBack() {
      this.$router.push('/lecturer/dashboard');
    },
    logout() {
      localStorage.clear();
      this.$router.push('/lecturer/login');
    },
    // async handleFileUpload(event) {
    //   const file = event.target.files[0];
    //   if (!file) return;

    //   const token = localStorage.getItem('token');
    //   const courseId = this.$route.params.id;

    //   const formData = new FormData();
    //   formData.append('file', file);
    //   formData.append('course_id', courseId);

    //   const res = await fetch('http://localhost:8080/api/lecturer/grade/import', {
    //     method: 'POST',
    //     headers: {
    //       'Authorization': `Bearer ${token}`
    //     },
    //     body: formData
    //   });

    //   const result = await res.json();
    //   this.message = result.message || 'Import completed!';
    //   await this.fetchStudents(courseId); // refresh the list
    // },
    // downloadExample() {
    //   const token = localStorage.getItem('token');

    //   fetch('http://localhost:8080/api/lecturer/grade/template', {
    //     headers: {
    //       'Authorization': `Bearer ${token}`
    //     }
    //   })
    //     .then(response => {
    //       if (!response.ok) throw new Error('Failed to download');
    //       return response.blob();
    //     })
    //     .then(blob => {
    //       const url = window.URL.createObjectURL(blob);
    //       const a = document.createElement('a');
    //       a.href = url;
    //       a.download = 'grade_template.xlsx';
    //       a.click();
    //       window.URL.revokeObjectURL(url);
    //     })
    //     .catch(err => {
    //       this.message = 'Download failed: ' + err.message;
    //     });
    // }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;700&display=swap');

.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', sans-serif;
  background-color: #f5f6fa;
}

/* Sidebar */
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

/* Main Content */
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
  /* 顶到最右边 */
}

.title {
  font-size: 1.8rem;
  color: #1e3a8a;
}

.subtitle {
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: #333;
}

/* Table */
.grade-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border: 1px solid #ccc;
  font-size: 14px;
}

.grade-table th,
.grade-table td {
  border: 1px solid #ddd;
  padding: 0.5rem;
  text-align: left;
}

.grade-table th {
  background-color: #e5e7eb;
}

/* Inputs */
.input-field {
  width: 100%;
  padding: 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

/* Buttons */
.btn {
  padding: 0.4rem 0.8rem;
  border: none;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
  color: white;
  text-decoration: none;
}

.btn.green {
  background-color: #28a745;
}

.btn.green:hover {
  background-color: #218838;
}

.btn.red {
  background-color: #dc3545;
}

.btn.red:hover {
  background-color: #c82333;
}

.message {
  margin-top: 1rem;
  color: #16a34a;
  font-weight: 600;
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

.import-box {
  margin: 1rem 0;
  background-color: #f1f5f9;
  padding: 1rem;
  border: 1px dashed #1e3a8a;
  border-radius: 8px;
}

.import-label {
  font-weight: bold;
  color: #1e3a8a;
  display: block;
  margin-bottom: 0.5rem;
}

.file-input {
  padding: 0.4rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
  max-width: 300px;
  background-color: white;
}
</style>
