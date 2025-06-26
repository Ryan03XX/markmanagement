<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Course: {{ course.name }}</h1>

    <section>
      <h2 class="text-lg font-semibold mb-2">
        Assessment Components ({{ totalWeight }}% of 100%)
      </h2>

      <form @submit.prevent="addComponent" class="mb-4 flex gap-2">
        <input
          v-model="newComponent.name"
          placeholder="Component Name"
          class="border p-2 w-1/3"
        />
        <input
          v-model.number="newComponent.weight"
          type="number"
          placeholder="Weight %"
          class="border p-2 w-24"
        />
        <button class="bg-blue-600 text-white px-4 py-2">Add</button>
      </form>

      <ul class="list-disc pl-6">
        <li v-for="c in components" :key="c.id">
          {{ c.name }} – {{ c.weight }}%
        </li>
      </ul>

      <p v-if="message" class="text-green-600 mt-4">{{ message }}</p>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      course: {},
      components: [],
      newComponent: { name: '', weight: '' },
      message: ''
    };
  },
  computed: {
    totalWeight() {
      return this.components.reduce((sum, c) => sum + parseFloat(c.weight), 0).toFixed(2);
    }
  },
  async created() {
    const courseId = this.$route.params.id;
    await this.fetchCourse(courseId);
    await this.fetchComponents(courseId);
  },
  methods: {
    async fetchCourse(id) {
      const res = await fetch(`http://localhost:8080/api/course/${id}`);
      const data = await res.json();
      this.course = data.course;
    },
    async fetchComponents(id) {
      const res = await fetch(`http://localhost:8080/api/course/${id}/components`);
      const data = await res.json();
      this.components = data.components;
    },
    async addComponent() {
      const courseId = this.$route.params.id;
      const res = await fetch(`http://localhost:8080/api/course/${courseId}/components`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.newComponent)
      });
      const data = await res.json();
      if (data.success) {
        this.components.push(data.component);
        this.newComponent.name = '';
        this.newComponent.weight = '';
        this.message = 'Component added successfully.';
      }
    }
  }
};
</script>
