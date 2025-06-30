<template>
  <div class="p-4 bg-white rounded shadow-md">
    <h3 class="text-lg font-semibold mb-2">📊 {{ student.name }}'s Performance</h3>
    <Bar :data="chartData" :options="chartOptions" />

    <div class="mt-4 text-sm text-gray-700">
      <p>📈 Mean: {{ mean.toFixed(2) }}</p>
      <p>📊 Median: {{ median }}</p>
      <p>📌 Mode: {{ mode.join(', ') || '-' }}</p>
    </div>
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

export default {
  name: 'StudentChart',
  components: { Bar },
  props: {
    student: Object,
    componentNames: Array
  },
  computed: {
    marks() {
      const scores = this.componentNames.map(name => {
        const comp = this.student.components?.find(c => c.name === name)
        return comp?.mark ?? 0
      })
      scores.push(this.student.final_mark)
      return scores
    },
    chartData() {
      return {
        labels: [...this.componentNames, 'Final'],
        datasets: [
          {
            label: 'Marks',
            backgroundColor: '#60a5fa',
            data: this.marks
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    },
    mean() {
      const sum = this.marks.reduce((a, b) => a + b, 0)
      return sum / this.marks.length
    },
    median() {
      const sorted = [...this.marks].sort((a, b) => a - b)
      const mid = Math.floor(sorted.length / 2)
      return sorted.length % 2 === 0
        ? (sorted[mid - 1] + sorted[mid]) / 2
        : sorted[mid]
    },
    mode() {
      const freq = {}
      this.marks.forEach(mark => {
        freq[mark] = (freq[mark] || 0) + 1
      })
      const maxFreq = Math.max(...Object.values(freq))
      return Object.keys(freq).filter(k => freq[k] === maxFreq)
    }
  }
}
</script>
