<template>
  <div>
    <h3 class="text-xl font-semibold mb-2">Available Courses</h3>
    <ul>
      <li v-for="course in availableCourses" :key="course.id" class="flex justify-between items-center mb-2 p-2 border rounded">
        <span>{{ course.code }} - {{ course.name }}</span>
        <button @click="enroll(course.id)" class="bg-green-600 text-white px-3 py-1 rounded">Enroll</button>
      </li>
    </ul>
    <p class="mt-2 text-green-600" v-if="message">{{ message }}</p>
  </div>
</template>

<script>
export default {
  props: ['studentId'],
  data() {
    return {
      availableCourses: [],
      message: ''
    }
  },
  methods: {
    async fetchCourses() {
      const res = await fetch('http://localhost:8080/api/courses/simple');
      const data = await res.json();
      if (data.success) {
        this.availableCourses = data.courses;
      }
    },
    async enroll(courseId) {
      const res = await fetch('http://localhost:8080/api/student/enroll', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: this.studentId, course_id: courseId })
      });
      const data = await res.json();
      this.message = data.message;
      this.$emit('enrolled');
    }
  },
  mounted() {
    this.fetchCourses();
  }
}
</script>
