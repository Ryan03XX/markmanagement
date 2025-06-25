<template>
  <div class="container">
  <button class="back-button" @click="goBack">← Back to Dashboard</button>
    <h2>Add New Course</h2>

    <!-- 表单 -->
    <form @submit.prevent="submitCourse" class="course-form">
      <input v-model="code" type="text" placeholder="Course Code" required />
      <input v-model="name" type="text" placeholder="Course Name" required />
      <button type="submit">Add Course</button>
    </form>

    <p v-if="message" class="message">{{ message }}</p>

    <!-- 课程列表表格 -->
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
</template>

<script>
export default {
  data() {
    return {
      code: '',
      name: '',
      message: '',
      courses: []
    }
  },
  methods: {
    goBack() {
      this.$router.push('/admin/dashboard'); // 👈 替换成你的 dashboard 路由路径
    },
    async submitCourse() {
      try {
        const res = await fetch('http://localhost:8080/api/courses', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ code: this.code, name: this.name })
        });

        if (!res.ok) {
          const errorText = await res.text();
          throw new Error(`Server Error: ${res.status} - ${errorText}`);
        }

        const result = await res.json();

        if (result.success) {
          this.message = result.message;
          this.code = '';
          this.name = '';
          await this.fetchCourses(); // ✅ 刷新列表
        } else {
          this.message = result.message || 'Unknown error';
        }
      } catch (error) {
        console.error("Error adding course:", error);
        this.message = 'Error: ' + error.message;
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
    }
  },
  mounted() {
    this.fetchCourses(); // ✅ 页面加载时获取课程列表
  }
}
</script>

<style>
.container {
  position: relative;
  max-width: 600px;
  margin: 40px auto;
  padding: 60px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fafafa;
}

h2, h3 {
  text-align: center;
  margin-bottom: 20px;
}

.course-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
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
  margin-top: 10px;
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
</style>
