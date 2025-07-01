<template>
  <div class="ranking-view">
    <h2 class="section-title">🏆 Courses Ranking</h2>
    <ul class="course-list">
      <li v-for="course in myCourses" :key="course.id" class="course-item">
        <button @click="selectCourse(course)" class="course-button">
          {{ course.code }} - {{ course.name }}
        </button>
      </li>
    </ul>

    <!-- 弹窗 -->
    <Modal v-if="selectedCourse" @close="selectedCourse = null">
      <template #default>
        <h3 class="text-xl font-bold mb-4">
          {{ selectedCourse.code }} - {{ selectedCourse.name }}
        </h3>
        <CourseRanking :courseId="selectedCourse.id" />
        <div class="mt-6 text-right">
          <!-- <button @click="selectedCourse = null" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Close
          </button> -->
        </div>
      </template>
    </Modal>
  </div>
</template>

<script>
import CourseRanking from './CourseRanking.vue'
import Modal from './ModalDialog.vue.vue'

export default {
  components: {
    CourseRanking,
    Modal
  },
  data() {
    return {
      studentId: localStorage.getItem('user_id'),
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


<style scoped>
.ranking-view {
  padding: 2rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.section-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
  color: #1e3a8a;
}

.course-list {
  list-style: none;
  padding: 0;
  margin: 0 0 2rem 0;
}

.course-item {
  border: 1px solid #ccc;
  border-radius: 6px;
  margin-bottom: 1rem;
  background-color: #fff;
  transition: box-shadow 0.3s;
}

.course-item:hover {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.course-button {
  background: none;
  border: none;
  padding: 1rem;
  width: 100%;
  text-align: left;
  font-size: 1rem;
  cursor: pointer;
}

.course-button:hover {
  background-color: #f0f4ff;
}

.ranking-section {
  margin-top: 2rem;
  border-top: 1px solid #ddd;
  padding-top: 1.5rem;
  display: flex;
  justify-content: center; /* 水平居中 */
}
</style>
