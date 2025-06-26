<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">Welcome, {{ studentName }}</h2>

    <h3 class="text-xl font-semibold mb-2">My Courses</h3>
    <ul class="mb-6">
      <li v-for="course in myCourses" :key="course.id" class="mb-2 p-2 border rounded">
        <button @click="selectCourse(course)" class="text-left w-full">
          {{ course.code }} - {{ course.name }}
        </button>
      </li>
    </ul>

    <EnrollCourse :studentId="studentId" @enrolled="loadMyCourses" />

    <!-- 成绩展示 -->
    <FinalResult
      v-if="selectedCourse"
      :studentId="studentId"
      :courseId="selectedCourse.id"
    />
  </div>
</template>

<script>
import EnrollCourse from './EnrollCourse.vue'
import FinalResult from './FinalResult.vue'

export default {
  components: { EnrollCourse, FinalResult },
  data() {
    return {
      studentId: localStorage.getItem('user_id'),
      studentName: localStorage.getItem('name'),
      myCourses: [],
      selectedCourse: null
    }
  },
  methods: {
    async loadMyCourses() {
      const res = await fetch(`http://localhost:8080/api/student/${this.studentId}/courses`);
      const data = await res.json();
      if (data.success) {
        this.myCourses = data.courses;
      }
    },
    selectCourse(course) {
      this.selectedCourse = course;
    }
  },
  mounted() {
    this.loadMyCourses();
  }
}
</script>
