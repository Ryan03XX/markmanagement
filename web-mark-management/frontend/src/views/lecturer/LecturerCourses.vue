<template>
  <div class="p-4">
    <h2 class="text-2xl font-bold mb-4">My Courses</h2>

    <ul>
      <li v-for="course in courses" :key="course.id" class="mb-4 border rounded p-4">
        <div class="flex justify-between items-center">
          <div>
            <strong>{{ course.code }}</strong> - {{ course.name }}
          </div>
          <button
            @click="toggleStudents(course.id)"
            class="text-blue-600 underline"
          >
            {{ expandedCourseId === course.id ? 'Hide' : 'View Students' }}
          </button>
        </div>

        <!-- 学生表格 -->
        <div v-if="expandedCourseId === course.id" class="mt-4">
          <table class="w-full border text-left">
            <thead>
              <tr>
                <th class="border p-2">Name</th>
                <th class="border p-2">Matric No</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in course.students" :key="student.id">
                <td class="border p-2">{{ student.name }}</td>
                <td class="border p-2">{{ student.matric_no }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </li>
    </ul>
  </div>
</template>
<script>
export default {
  data() {
    return {
      courses: [],
      expandedCourseId: null
    };
  },
  async created() {
    const lecturerId = localStorage.getItem('user_id');
    const res = await fetch(`http://localhost:8080/api/lecturer/courses/${lecturerId}`);
    const data = await res.json();
    if (data.success) {
      this.courses = data.courses;
    }
  },
  methods: {
    async toggleStudents(courseId) {
      if (this.expandedCourseId === courseId) {
        this.expandedCourseId = null;
        return;
      }

      this.expandedCourseId = courseId;

      const course = this.courses.find(c => c.id === courseId);
      if (!course.students) {
        const res = await fetch(`http://localhost:8080/api/lecturer/course/${courseId}/students`);
        const data = await res.json();
        if (data.success) {
          course.students = data.students;
        } else {
          course.students = [];
        }
      }
    }
  }
};
</script>
