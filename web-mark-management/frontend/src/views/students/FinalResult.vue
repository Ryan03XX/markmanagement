<template>
  <div class="final-result-card" v-if="loaded">
    <h3 class="final-title">📊 Final Result</h3>

    <table class="result-table">
      <thead>
        <tr>
          <th>Component</th>
          <th>Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(comp, index) in componentResults" :key="index">
          <td>{{ comp.component_name }}</td>
          <td>{{ comp.mark }}</td>
        </tr>
        <tr>
          <td><strong>Final Exam</strong></td>
          <td>{{ finalResult.final_exam_mark }}</td>
        </tr>
        <tr>
          <td><strong>Total Final Mark</strong></td>
          <td>{{ finalResult.final_mark }}</td>
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
      finalResult: {},
      componentResults: [],
      loaded: false
    };
  },
  async mounted() {
    const res = await fetch(`http://localhost:8080/api/student/${this.studentId}/course/${this.courseId}/result`);
    const data = await res.json();
    if (data.success) {
      this.finalResult = data.final_result || {};
      this.componentResults = data.component_results || [];
      this.loaded = true;
    }
  }
};
</script>

<style scoped>
.final-result-card {
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  max-width: 600px;
  margin: 20px auto;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  transition: all 0.3s ease;
}

.final-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 20px;
  color: #1e40af;
  text-align: center;
}

.result-table {
  width: 100%;
  border-collapse: collapse;
}

.result-table th,
.result-table td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
  font-size: 1rem;
  color: #374151;
}

.result-table th {
  background-color: #f3f4f6;
  font-weight: 600;
  color: #111827;
}

.result-table tr:last-child td {
  font-weight: bold;
}
</style>
