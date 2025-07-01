<template>
  <div>
    <h3 class="text-xl font-semibold mb-2">🏆 Course Ranking</h3>
    <table class="ranking-table">
      <thead>
        <tr>
          <th class="rank">Rank</th>
          <th>Name</th>
          <th v-for="(compName, index) in componentNames" :key="index">
            {{ compName }}
          </th>
          <th>Final Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(student, index) in ranking"
          :key="student.student_id"
          :class="{ 'self-mark': student.student_id === currentUserId }"
        >
          <td class="rank">{{ index + 1 }}</td>
          <td>{{ student.name }}</td>
          <td v-for="(compName, idx) in componentNames" :key="idx">
            {{ getComponentMark(student.components, compName) }}
          </td>
          <td>{{ student.final_mark }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="myFeedback" class="mt-4 p-4 border rounded bg-blue-50">
      <h4 class="text-lg font-bold mb-1">📝 Your Feedback</h4>
      <p>{{ myFeedback }}</p>
    </div>

    <div v-if="ranking.length === 0" class="mt-2 text-gray-500">
      No ranking data available.
    </div>
  </div>
</template>

<script>
export default {
  props: ["courseId"],
  data() {
    return {
      ranking: [],
      componentNames: [],
      currentUserId: null,
      myFeedback: null,
    };
  },
  watch: {
    courseId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.fetchRanking();
        }
      },
    },
  },
  mounted() {
    // 假设你在登录成功时将用户 ID 存到 localStorage 里（例如 "user_id"）
    this.currentUserId = parseInt(localStorage.getItem("user_id"));
  },
  methods: {
    async fetchRanking() {
      try {
        const res = await fetch(
          `http://localhost:8080/api/student/course-ranking/${this.courseId}`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          }
        );
        const data = await res.json();
        if (data.success) {
          this.ranking = data.ranking;
          this.extractComponentNames();
          const me = data.ranking.find(
            (s) => s.student_id === this.currentUserId
          );
          this.myFeedback = me?.feedback || null;
        } else {
          this.ranking = [];
          this.componentNames = [];
          this.myFeedback = null;
        }
      } catch (err) {
        console.error("Failed to fetch ranking:", err);
        this.ranking = [];
        this.componentNames = [];
      }
    },
    extractComponentNames() {
      const nameSet = new Set();
      this.ranking.forEach((student) => {
        student.components.forEach((comp) => {
          nameSet.add(comp.name);
        });
      });
      this.componentNames = Array.from(nameSet);
    },
    getComponentMark(components, name) {
      const found = components.find((comp) => comp.name === name);
      return found ? found.mark : "-";
    },
  },
};
</script>

<style scoped>
table {
  border: 1px solid #ccc;
}

.self-mark {
  background-color: #e0f7fa; /* Tailwind 的 bg-cyan-100 */
}

.ranking-table {
  width: 100%;
  max-width: 800px;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  overflow: hidden;
  font-size: 0.95rem;
}

.ranking-table th,
.ranking-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.ranking-table th {
  background-color: #f3f4f6;
  color: #1e3a8a;
  font-weight: 600;
}

.ranking-table tr:hover {
  background-color: #f9fafb;
}

.ranking-table td.rank {
  font-weight: bold;
  color: #2563eb;
  text-align: center;
  width: 60px;
}
</style>
