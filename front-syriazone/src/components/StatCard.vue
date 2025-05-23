<template>
  <div class="stat-card" :style="`border-left: 4px solid var(--${color})`">
    <div class="stat-icon" :style="`background-color: var(--${color}-light)`">
      <span class="mdi" :class="icon"></span>
    </div>
    <div class="stat-content">
      <h3>{{ title }}</h3>
      <p>{{ value }}</p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: String,
    value: [String, Number],
    icon: String,
    color: {
      type: String,
      default: 'blue',
    },
  },
}
</script>

<style scoped>
.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 15px;
}

.stat-icon span {
  font-size: 24px;
  color: var(--text-color);
}

.stat-content h3 {
  margin: 0 0 5px 0;
  font-size: 16px;
  color: #666;
}

.stat-content p {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

:root {
  --blue: #2196f3;
  --blue-light: #e3f2fd;
  --green: #4caf50;
  --green-light: #e8f5e9;
  --orange: #ff9800;
  --orange-light: #fff3e0;
  --purple: #9c27b0;
  --purple-light: #f3e5f5;
  --teal: #009688;
  --teal-light: #e0f2f1;
  --red: #f44336;
  --red-light: #ffebee;
  --text-color: #333;
}
</style>
