<script setup>
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

const data = {
  labels: ['Men', 'Women', 'Kids', 'Accessories'],
  datasets: [
    {
      backgroundColor: ['#4f46e5', '#ec4899', '#f59e0b', '#10b981'],
      data: [40, 20, 30, 10]
    }
  ]
}

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
        position: 'bottom',
        labels: {
            usePointStyle: true,
            padding: 20
        }
    }
  }
}
</script>

<template>
    <div class="h-72">
        <Doughnut :data="data" :options="options" />
    </div>
</template>
