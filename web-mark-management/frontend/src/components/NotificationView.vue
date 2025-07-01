<template>
  <div>
    <h2>📨 Notifications</h2>
    <ul>
      <li v-for="n in notifications" :key="n.id">
        <strong v-if="!n.is_read">[NEW]</strong> {{ n.message }} <small>({{ formatDate(n.created_at) }})</small>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      notifications: []
    }
  },
  async created() {
    const studentId = localStorage.getItem('id')
    console.log('Fetching notifications for student ID:', studentId)
    const res = await fetch(`http://localhost:8080/api/student/${studentId}/notifications`)
    const data = await res.json()
    if (data.success) {
      this.notifications = data.notifications
    }
  },
  methods: {
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString()
    }
  }
}
</script>
