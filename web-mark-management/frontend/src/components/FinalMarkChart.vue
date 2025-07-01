<template>
  <div>
    <h2 class="text-xl font-semibold text-center my-4">📊 Final Mark Distribution</h2>
    <Bar v-if="students.length" :data="chartData" :options="chartOptions" />
    <p v-else class="text-center text-gray-500 mt-4">No student data available.</p>
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'

// 注册 chart.js 组件
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'FinalMarkChart',
  components: {
    Bar,
  },
  props: {
    students: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    chartData() {
      if (!Array.isArray(this.students) || this.students.length === 0) return null
    const sortedStudents = [...this.students].sort((a, b) => a.final_mark - b.final_mark);

      return {
        labels: sortedStudents.map((s) => s.name),
        datasets: [
          {
            label: 'Final Marks',
            data: sortedStudents.map((s) => s.final_mark),
            backgroundColor: sortedStudents.map((s) =>
              s.final_mark < 40 ? '#f87171' : '#2563eb'
            ), // 红色代表不及格
          },
        ],
      }
    },
    chartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: (context) => `Final Mark: ${context.raw}`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: {
              display: true,
              text: 'Final Mark',
            },
          },
          x: {
            ticks: {
              callback: (value, index) => {
                const label = this.chartData?.labels[index]
                return label.length > 10 ? label.slice(0, 10) + '…' : label
              },
            },
          },
        },
      }
    },
  },
}
</script>

<style scoped>
/* 可选样式 */
</style>
