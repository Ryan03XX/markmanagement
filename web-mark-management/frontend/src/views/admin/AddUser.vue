<template>
  <div class="add-user-container">
    <button class="back-button" @click="goBack">← Back to Dashboard</button>
    <h2>Add New User</h2>

    <div class="form-group">
      <label>Name:</label>
      <input type="text" v-model="name" required />
    </div>

    <div class="form-group">
      <label>Matric Number:</label>
      <input type="text" v-model="matricNo" required />
    </div>

    <div class="form-group">
      <label>Password:</label>
      <input type="password" v-model="password" required />
    </div>

    <div class="form-group">
      <label>Role:</label>
      <select v-model="role" required>
        <option disabled value="">-- Select Role --</option>
        <option value="admin">Admin</option>
        <option value="lecturer">Lecturer</option>
        <option value="student">Student</option>
      </select>
    </div>

    <button @click="register">Register</button>
    <p v-if="message" :class="{ success: success, error: !success }">{{ message }}</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      name: '',
      matricNo: '',
      password: '',
      role: '',
      message: '',
      success: false
    }
  },
  methods: {
    goBack() {
        this.$router.push('/admin/dashboard'); // 👈 替换成你的 dashboard 路由路径
        },
    async register() {
      try {
        const res = await fetch('http://localhost:8080/api/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            name: this.name,
            matric_no: this.matricNo,
            password: this.password,
            role: this.role
          })
        });

        const result = await res.json();
        this.message = result.message;
        this.success = result.success;

        if (result.success) {
          // Reset form
          this.name = '';
          this.matricNo = '';
          this.password = '';
          this.role = '';
        }
      } catch (err) {
        this.message = 'Network error';
        this.success = false;
        console.error(err);
      }
    }
  }
}
</script>

<style scoped>
.add-user-container {
  position: relative;
  max-width: 600px;
  margin: 40px auto;
  padding: 60px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background: #fafafa;
}

.back-button {
  display: inline-block;
  margin-bottom: 20px;
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 8px 16px;
  font-size: 14px;
  background-color: #ccc;
  color: #333;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.back-button:hover {
  background-color: #bbb;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

input,
select {
  padding: 10px;
  font-size: 16px;
}

button {
  background-color: #007bff;
  color: white;
  padding: 10px;
  font-size: 16px;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

button:hover {
  background-color: #0056b3;
}

.success {
  color: green;
  text-align: center;
  margin-top: 10px;
}

.error {
  color: red;
  text-align: center;
  margin-top: 10px;
}
</style>
