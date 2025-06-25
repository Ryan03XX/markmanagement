<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Welcome, {{ studentName }}</h2>

    <h3 class="text-xl font-semibold mb-2">My Courses</h3>
    <ul class="mb-6">
      <li v-for="course in myCourses" :key="course.id" class="mb-2 p-2 border rounded">
        {{ course.code }} - {{ course.name }}
      </li>
    </ul>

    <EnrollCourse :studentId="studentId" @enrolled="loadMyCourses" />
  </div>
</template>

<script>
import EnrollCourse from './EnrollCourse.vue'

export default {
  components: { EnrollCourse },
  data() {
    return {
      studentId: localStorage.getItem('user_id'),
      studentName: localStorage.getItem('name'),
      myCourses: []
    }
  },
  methods: {
    async loadMyCourses() {
      const res = await fetch(`http://localhost:8080/api/student/${this.studentId}/courses`);
      const data = await res.json();
      if (data.success) {
        this.myCourses = data.courses;
      }
    }
  },
  mounted() {
    this.loadMyCourses();
  }
}
</script>
