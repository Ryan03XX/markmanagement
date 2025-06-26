<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Students in Course</h2>
    <table class="w-full border text-sm">
      <thead>
        <tr class="bg-gray-200">
          <th class="p-2 border">Matric No</th>
          <th class="p-2 border">Name</th>
          <th
            v-for="component in components"
            :key="component.id"
            class="p-2 border"
          >
            {{ component.name }} ({{ component.weight }}%)
          </th>
          <th class="p-2 border">Final Exam (30%)</th>
          <th class="p-2 border">Final Mark</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td class="border p-2">{{ student.matric_no }}</td>
          <td class="border p-2">{{ student.name }}</td>
          
          <!-- Dynamic components scores -->
          <td
            v-for="component in components"
            :key="component.id"
            class="border p-2"
          >
            <input
              type="number"
              class="border p-1 w-20"
              v-model.number="student.scores[component.id]"
            />
          </td>

          <td class="border p-2">
            <input
              v-model.number="student.final_exam_mark"
              type="number"
              class="border p-1 w-20"
            />
          </td>

          <td class="border p-2">
            {{ calculateFinal(student) }}
          </td>

          <td class="border p-2">
            <button
              class="bg-green-500 text-white px-2 py-1"
              @click="saveGrade(student)"
            >
              Save
            </button>
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
      components: [],
      students: [],
      message: ''
    }
  },
  async created() {
    const courseId = this.$route.params.id;
    await this.fetchComponents(courseId);
    await this.fetchStudents(courseId);
  },
  methods: {
    async fetchComponents(courseId) {
      const res = await fetch(`http://localhost:8080/api/course/${courseId}/components`);
      const data = await res.json();
      this.components = data.components;
    },
    async fetchStudents(courseId) {
      const res = await fetch(`http://localhost:8080/api/lecturer/course/${courseId}/students`);
      const data = await res.json();

      // Each student gets an empty scores object, e.g., { [component_id]: score }
      this.students = data.students.map(student => {
        // 把数组形式的 scores 转换成 object：{ [component_id]: mark }
        const scoreObj = {};
        (student.scores || []).forEach(s => {
          scoreObj[s.component_id] = s.mark;
        });

        return {
          ...student,
          scores: scoreObj,
          final_exam_mark: student.final_exam_mark || 0
        };
      });
    },
    calculateFinal(student) {
      let componentTotal = 0;
      this.components.forEach(c => {
        const score = parseFloat(student.scores[c.id]) || 0;
        componentTotal += score * (c.weight / 100);
      });

      // 70% component + 30% final
      const componentWeighted = componentTotal * 0.7;
      const finalExamWeighted = (parseFloat(student.final_exam_mark) || 0) * 0.3;
      return (componentWeighted + finalExamWeighted).toFixed(2);
    },
    async saveGrade(student) {
      // 确保 component_scores 的 key 是整数
      const component_scores = {};
      for (const key in student.scores) {
        component_scores[parseInt(key)] = student.scores[key];
      }

      const res = await fetch('http://localhost:8080/api/lecturer/grade', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          course_id: this.$route.params.id,
          student_id: student.id,
          component_scores: component_scores,
          final_exam_mark: student.final_exam_mark
        })
      });

      const data = await res.json();
      this.message = data.message;
    }
  }
}
</script>
