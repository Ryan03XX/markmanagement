<template>
  <div class="dashboard-container">
    <h1 class="title">Lecturer Dashboard</h1>

    <h2 class="subtitle">My Courses</h2>
    <ul class="course-list">
      <li v-for="course in courses" :key="course.id" class="course-card">
        <p class="course-name">{{ course.code }} - {{ course.name }}</p>
        <div class="button-group">
          <router-link
            :to="`/lecturer/course/${course.id}`"
            class="btn btn-green"
          >
            📝 Grade Students
          </router-link>
          <router-link
            :to="`/lecturer/course-detail/${course.id}`"
            class="btn btn-blue"
          >
            ➕ Add Assessment Component
          </router-link>
        </div>
      </li>
    </ul>
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
  }
}
</script>

<style>
.dashboard-container {
  padding: 24px;
  font-family: Arial, sans-serif;
}

.title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 16px;
}

.subtitle {
  font-size: 20px;
  margin-bottom: 12px;
}

.course-list {
  list-style: none;
  padding: 0;
}

.course-card {
  border: 1px solid #ccc;
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 16px;
}

.course-name {
  font-weight: bold;
  margin-bottom: 8px;
}

.button-group {
  display: flex;
  gap: 12px;
}

.btn {
  padding: 8px 16px;
  border: none;
  text-decoration: none;
  color: white;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.btn-green {
  background-color: #28a745;
}

.btn-green:hover {
  background-color: #218838;
}

.btn-blue {
  background-color: #007bff;
}

.btn-blue:hover {
  background-color: #0056b3;
}
</style>
