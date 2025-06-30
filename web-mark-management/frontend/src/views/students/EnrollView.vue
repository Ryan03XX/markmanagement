<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">📚 Enroll in Courses</h2>
    <EnrollCourse :studentId="studentId" @enrolled="reload" />
  </div>
</template>

<script>
import EnrollCourse from './EnrollCourse.vue'

export default {
  components: { EnrollCourse },
  data() {
    return {
      studentId: localStorage.getItem('user_id')
    }
  },
  methods: {
    reload() {
      alert('Enrolled successfully!')
    }
  }
}
</script>
