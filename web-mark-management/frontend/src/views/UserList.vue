<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">用户列表</h1>
    <ul>
      <li v-for="user in users" :key="user.id">
        {{ user.name }} - {{ user.role }}
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      users: []
    };
  },
  mounted() {
    axios.get('http://localhost/backend/public/api/users')
      .then(res => {
        this.users = res.data;
      })
      .catch(err => {
        console.error('获取用户失败:', err);
      });
  }
}
</script>
