<template>
  <div class="what-if-container" v-if="loaded">
    <h3 class="title">🎯 What-If Grade Simulator</h3>

    <table class="grade-table">
      <thead>
        <tr>
          <th>Component</th>
          <th>Weight (%)</th>
          <th>Mark (editable)</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(comp, index) in editableComponentResults" :key="index">
          <td>{{ comp.component_name }}</td>
          <td>{{ comp.weight }}</td>
          <td>
            <input v-model.number="comp.mark" type="number" min="0" max="100" />
          </td>
        </tr>

        <tr class="final-exam-row">
          <td><strong>Final Exam</strong></td>
          <td>30%</td>
          <td>
            <input v-model.number="editableFinalExamMark" type="number" min="0" max="100" />
          </td>
        </tr>

        <tr class="final-mark-row">
          <td><strong>Total Final Mark</strong></td>
          <td colspan="2">{{ computedFinalMark }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: ['studentId', 'courseId'],
  data() {
    return {
      editableComponentResults: [],
      editableFinalExamMark: 0,
      loaded: false,
    };
  },
  computed: {
    computedFinalMark() {
      let componentMark = 0;
      this.editableComponentResults.forEach(comp => {
        const weight = Number(comp.weight) || 0;
        const mark = Number(comp.mark) || 0;
        componentMark += mark * (weight / 100);
      });
      return Math.round((componentMark * 0.7 + this.editableFinalExamMark * 0.3) * 100) / 100;
    }
  },
  async mounted() {
    const res = await fetch(`http://localhost:8080/api/student/${this.studentId}/course/${this.courseId}/what-if`);
    const data = await res.json();
    if (data.success) {
      this.editableComponentResults = data.component_results.map(c => ({
        ...c,
        mark: c.mark ?? 0
      }));
      this.editableFinalExamMark = data.final_exam_mark ?? 0;
      this.loaded = true;
    }
  }
};
</script>

<style scoped>
.what-if-container {
  max-width: 700px;
  margin: 40px auto;
  background-color: #fff;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.title {
  text-align: center;
  font-size: 24px;
  color: #4a5bb2;
  margin-bottom: 20px;
}

.grade-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
}

.grade-table th,
.grade-table td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: center;
}

.grade-table th {
  background-color: #f0f4ff;
  color: #333;
}

.grade-table tr:nth-child(even) {
  background-color: #fafafa;
}

.grade-table input {
  width: 60px;
  padding: 5px 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  text-align: center;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.grade-table input:focus {
  border-color: #4a5bb2;
  box-shadow: 0 0 4px rgba(74, 91, 178, 0.4);
  outline: none;
}

.final-exam-row {
  background-color: #fffbe6;
}

.final-mark-row {
  background-color: #e9fce9;
  color: #1a7a1f;
  font-weight: bold;
  font-size: 18px;
}
</style>
