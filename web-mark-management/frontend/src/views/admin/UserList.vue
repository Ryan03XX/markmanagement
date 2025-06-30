<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">🎓 Admin Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/admin/dashboard')">👥Dashboard</li>
        <li @click="$router.push('/admin/courses/add')">📚 Manage Courses</li>
        <li @click="$router.push('/admin/courses/assign')">👨‍🏫 Assign Lecturers</li>
        <li @click="$router.push('/admin/users/add')">🎓 Add Users</li>
        <li @click="$router.push('/admin/users/manage')">👥 Manage Users</li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- 主内容 -->
    <div class="main-content">
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ user.name }} ({{ user.role }})     </span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <main class="dashboard">
        <h2>User List</h2>

        <table class="course-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Matric No</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email || '-' }}</td>
              <td>{{ user.role }}</td>
              <td>{{ user.matric_no || '-' }}</td>
              <td>
                <button class="btn-red" @click="confirmDelete(user.id)">Delete</button>
                <button class="btn-blue" @click="openPasswordModal(user)">Change Password</button>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="users.length === 0" class="message">No users found.</p>

        <!-- 密码弹窗 -->
        <div
          v-if="showModal"
          class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50"
        >
          <div class="bg-white p-6 rounded-lg w-80">
            <h3 class="text-lg font-semibold mb-4">Change Password</h3>
            <p class="mb-2">User: <strong>{{ selectedUser.name }}</strong></p>
            <input
              type="password"
              v-model="newPassword"
              placeholder="Enter new password"
              class="border w-full px-3 py-2 mb-4 rounded"
            />
            <div class="flex justify-end space-x-2">
              <button class="btn-gray" @click="showModal = false">Cancel</button>
              <button class="btn-green" @click="changePassword">Save</button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const users = ref([])
const user = ref({})
const showModal = ref(false)
const selectedUser = ref({})
const newPassword = ref('')

const fetchUsers = async () => {
  try {
    const res = await axios.get('http://localhost:8080/api/users', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    users.value = res.data.users
  } catch (err) {
    console.error('Failed to fetch users:', err)
  }
}

const logout = () => {
  localStorage.removeItem('user')
  router.push('/admin/login')
}

const confirmDelete = async (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    try {
      await axios.delete(`http://localhost:8080/api/users/${id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
      users.value = users.value.filter(user => user.id !== id)
      alert('User deleted successfully.')
    } catch (err) {
      console.error('Delete failed:', err)
      alert('Failed to delete user.')
    }
  }
}

const openPasswordModal = (u) => {
  selectedUser.value = u
  newPassword.value = ''
  showModal.value = true
}

const changePassword = async () => {
  if (!newPassword.value) {
    alert('Please enter a new password.')
    return
  }
  try {
    await axios.put(`http://localhost:8080/api/users/${selectedUser.value.id}/password`, {
      password: newPassword.value
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    })
    alert('Password updated successfully.')
    showModal.value = false
  } catch (err) {
    console.error('Password update failed:', err)
    alert('Failed to update password.')
  }
}

onMounted(() => {
  const savedUser = JSON.parse(localStorage.getItem('user'))
  if (!savedUser || savedUser.role !== 'admin') {
    router.push('/admin/login')
  } else {
    user.value = savedUser
    fetchUsers()
  }
})
</script>

<style scoped>
/* @import './admin-shared.css';  */

.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
}

.sidebar {
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
}
.logo {
  font-size: 1.2rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 2rem;
}
.nav-links {
  list-style: none;
  padding: 0;
}
.nav-links li {
  padding: 0.8rem 1rem;
  background-color: #3749a3;
  margin-bottom: 0.5rem;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}
.nav-links li:hover {
  background-color: #475aca;
}

.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.navbar {
  background-color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.navbar button {
  background-color: #ef4444;
  color: white;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 5px;
  cursor: pointer;
}
.navbar button:hover {
  background-color: #dc2626;
}

.dashboard {
  padding: 2rem;
}
.dashboard h2 {
  text-align: center;
  margin-bottom: 20px;
}

.course-table {
  width: 100%;
  border-collapse: collapse;
}
.course-table th,
.course-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}
.course-table th {
  background-color: #f0f0f0;
}
.message {
  text-align: center;
  color: gray;
  margin-top: 10px;
}

.btn-red {
  background-color: #ef4444;
  color: white;
  padding: 5px 10px;
  border: none;
  margin-right: 5px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-red:hover {
  background-color: #dc2626;
}

.btn-blue {
  background-color: #3b82f6;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-blue:hover {
  background-color: #2563eb;
}

.btn-gray {
  background-color: #6b7280;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
}
.btn-green {
  background-color: #10b981;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
}

.logout-section {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.logout-section button {
  width: 100%;
  padding: 0.6rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.logout-section button:hover {
  background-color: #dc2626;
}
</style>
