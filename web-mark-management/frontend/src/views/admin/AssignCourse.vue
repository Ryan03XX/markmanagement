<template>
  <div class="assign-container">
    <button class="back-button" @click="goBack">← Back to Dashboard</button>

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
  </div>
</template>

<script>
export default {
  data() {
    return {
      courses: [],
      lecturers: [],
      selectedCourseId: null,
      selectedLecturerId: null,
      message: ''
    }
  },
  async created() {
    const courseRes = await fetch('http://localhost:8080/api/courses');
    this.courses = await courseRes.json();

    const lecturerRes = await fetch('http://localhost:8080/api/lecturers');
    this.lecturers = await lecturerRes.json();
  },
  methods: {
    async assign() {
      const res = await fetch('http://localhost:8080/api/assign-course', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          course_id: this.selectedCourseId,
          lecturer_id: this.selectedLecturerId
        })
      });

      const result = await res.json();

      if (result.success) {
        this.message = result.message;

        // 👇 调用 fetchCourses() 以刷新课程列表
        await this.fetchCourses();

        // 可选：清空选择框
        this.selectedCourseId = null;
        this.selectedLecturerId = null;
      } else {
        this.message = result.message || 'Failed to assign course.';
      }
    },
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:8080/api/courses');
        const data = await res.json();
        this.courses = data;
      } catch (error) {
        console.error('Failed to fetch courses:', error);
      }
    },
    goBack() {
      this.$router.push('/admin/dashboard'); // 修改为你实际的路由路径
    }
  }
}
</script>

<style>
.assign-container {
  position: relative;
  max-width: 600px;
  margin: 40px auto;
  padding: 60px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fafafa;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.back-button {
  display: inline-block;
  margin-bottom: 20px;
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 8px 16px;
  font-size: 14px;
  background-color: #ccc;
  color: #333;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.back-button:hover {
  background-color: #bbb;
}

.form-section {
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
</style>
