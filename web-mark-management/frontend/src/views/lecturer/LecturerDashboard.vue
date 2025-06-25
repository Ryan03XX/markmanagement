<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Lecturer Dashboard</h1>

    <h2 class="text-xl font-semibold mb-2">My Courses</h2>
    <ul>
      <li
        v-for="course in courses"
        :key="course.id"
        class="mb-2"
      >
        <router-link
          :to="`/lecturer/course/${course.id}`"
          class="text-blue-600 underline"
        >
          {{ course.code }} - {{ course.name }} ➡ Grade Students
        </router-link>
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
