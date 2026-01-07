<script setup>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const data = {
  labels: ['Cotton Shirt', 'Denim Jeans', 'Silk Saree', 'Leather Belt', 'Polyester T-Shirt'],
  datasets: [
    {
      label: 'Units Sold',
      backgroundColor: '#4f46e5',
      data: [40, 35, 28, 22, 18]
    }
  ]
}

const options = {
  indexAxis: 'y', // Horizontal bar chart
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
        display: false
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
            display: false
        }
    }
  }
}
</script>

<template>
    <div class="h-72">
        <Bar :data="data" :options="options" />
    </div>
</template>
