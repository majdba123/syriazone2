<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { Chart, PieController, ArcElement, Tooltip, Legend } from 'chart.js'
import { ref, onMounted, watch } from 'vue'

// تسجيل المكونات المطلوبة
Chart.register(PieController, ArcElement, Tooltip, Legend)

export default {
  name: 'PieChart',
  props: {
    chartData: {
      type: Object,
      required: true,
    },
    options: {
      type: Object,
      default: () => ({}),
    },
  },
  setup(props) {
    const chartCanvas = ref(null)
    let chartInstance = null

    const renderChart = () => {
      if (chartInstance) {
        chartInstance.destroy()
      }

      if (chartCanvas.value) {
        chartInstance = new Chart(chartCanvas.value, {
          type: 'pie',
          data: props.chartData,
          options: props.options,
        })
      }
    }

    onMounted(renderChart)
    watch(() => props.chartData, renderChart, { deep: true })

    return {
      chartCanvas,
    }
  },
}
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 300px;
}
</style>
