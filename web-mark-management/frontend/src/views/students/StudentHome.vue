<template>
  <div>
    <h1 class="text-2xl font-bold text-blue-900 mb-4">🎉 Student Dashboard</h1>
    <p>Welcome back! Here's your student overview.</p>

    <div v-if="chartData">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
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
  LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'StudentHome',
  components: { Bar },
  data() {
    return {
      chartData: null,
      chartOptions: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          title: {
            display: true,
            text: 'Final Marks by Course'
          }
        }
      }
    }
  },
  mounted() {
    const studentId = localStorage.getItem('user_id');
    fetch(`http://localhost:8080/api/student/${studentId}/final-marks`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const labels = data.data.map(item => item.course_name)
          const marks = data.data.map(item => item.final_mark)

          this.chartData = {
            labels,
            datasets: [
              {
                label: 'Final Mark',
                data: marks,
                backgroundColor: '#3B82F6'
              }
            ]
          }
        }
      })
      .catch(error => {
        console.error('Error fetching final marks:', error)
      })
  }
}
</script>

<style scoped>
canvas {
  width: 100% !important;
  height: auto;
  margin-top: 2rem;
}
</style>
