<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const data = {
  labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
  datasets: [
    {
      label: 'Revenue',
      backgroundColor: 'rgba(79, 70, 229, 0.2)', // Indigo-500 with opacity
      borderColor: '#4338ca', // Indigo-700
      pointBackgroundColor: '#4338ca',
      pointBorderColor: '#fff',
      pointHoverBackgroundColor: '#fff',
      pointHoverBorderColor: '#4338ca',
      fill: true,
      tension: 0.4, // Smooth curves
      data: [40, 39, 10, 40, 39, 80, 40]
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
        display: false 
    },
    tooltip: {
        backgroundColor: '#1f2937',
        padding: 12,
        cornerRadius: 8,
    }
  },
  scales: {
    x: {
        grid: {
            display: false
        }
    },
    y: {
        grid: {
            borderDash: [5, 5],
            color: '#e5e7eb'
        },
        border: {
             display: false
        }
    }
  }
}
</script>

<template>
    <div class="h-72">
        <Line :data="data" :options="options" />
    </div>
</template>
