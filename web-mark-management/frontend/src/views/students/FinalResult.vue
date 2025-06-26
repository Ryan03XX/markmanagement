<template>
  <div class="border p-4 mt-4 rounded bg-gray-50" v-if="loaded">
    <h3 class="text-lg font-semibold mb-2">Final Result</h3>

    <div v-if="componentResults.length">
      <h4 class="font-medium mb-1">Component Scores:</h4>
      <ul class="mb-2 list-disc list-inside">
        <li v-for="(comp, index) in componentResults" :key="index">
          {{ comp.component_name }}: {{ comp.mark }}
        </li>
      </ul>
    </div>

    <p><strong>Final Exam:</strong> {{ finalResult.final_exam_mark }}</p>
    <p><strong>Total Final Mark:</strong> {{ finalResult.final_mark }}</p>
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
