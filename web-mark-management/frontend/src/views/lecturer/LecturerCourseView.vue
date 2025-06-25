<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Students in Course</h2>
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2 border">Matric No</th>
          <th class="p-2 border">Name</th>
          <th class="p-2 border">Component (70%)</th>
          <th class="p-2 border">Final Exam (30%)</th>
          <th class="p-2 border">Final Mark</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td class="border p-2">{{ student.matric_no }}</td>
          <td class="border p-2">{{ student.name }}</td>
          <td class="border p-2">
            <input v-model.number="student.component_mark" type="number" class="border p-1 w-20" />
          </td>
          <td class="border p-2">
            <input v-model.number="student.final_exam_mark" type="number" class="border p-1 w-20" />
          </td>
          <td class="border p-2">{{ calculateFinal(student) }}</td>
          <td class="border p-2">
            <button class="bg-green-500 text-white px-2 py-1" @click="saveGrade(student)">Save</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-if="message" class="mt-4 text-green-600">{{ message }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      students: [],
      message: ''
    }
  },
  methods: {
    async fetchStudents() {
      const res = await fetch(`http://localhost:8080/api/lecturer/course/${this.$route.params.id}/students`);
      const data = await res.json();
      if (data.success) {
        this.students = data.students;
      }
    },
    calculateFinal(student) {
      const c = Number(student.component_mark || 0);
      const f = Number(student.final_exam_mark || 0);
      return (c * 0.7 + f * 0.3).toFixed(2);
    },
    async saveGrade(student) {
      const res = await fetch('http://localhost:8080/api/lecturer/grade', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          course_id: this.$route.params.id,
          student_id: student.student_id,
          component_mark: student.component_mark,
          final_exam_mark: student.final_exam_mark
        })
      });
      const data = await res.json();
      this.message = data.message;
    }
  },
  created() {
    this.fetchStudents();
  }
}
</script>
