<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">📘 Lecturer Panel</div>
      <ul class="nav-links">
        <li @click="$router.push('/lecturer/dashboard')">📚 My Courses</li>
        <li @click="$router.push('/lecturer/remark-requests')">📝 Remark Requests</li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Navbar -->
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, Lecturer</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
      </header>

      <!-- Remark Requests -->
      <main class="dashboard">
        <h1>📬 Remark Requests</h1>

        <table class="requests-table">
          <thead>
            <tr>
              <th>Student</th>
              <th>Course</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="req in requests" :key="req.id">
              <td>{{ req.student_name }}</td>
              <td>{{ req.course_name }}</td>
              <td>
                <span :class="'status-tag ' + req.status">
                  {{ req.status === 'approved' ? 'Done Remark' : req.status }}
                </span>
              </td>
              <td>
                <template v-if="req.status === 'pending'">
                  <button class="btn green" @click="respond(req.id, 'approved')">✅ Remark</button>
                  <button class="btn red" @click="respond(req.id, 'rejected')">❌ Reject</button>
                </template>
                <template v-else>-</template>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="requests.length === 0" class="no-course">No requests found.</p>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      requests: []
    };
  },
  async mounted() {
    const role = localStorage.getItem('role');
    if (role !== 'lecturer') {
      this.$router.push('/lecturer/login');
      return;
    }

    const res = await fetch('http://localhost:8080/api/lecturer/remark-requests');
    const data = await res.json();
    if (data.success) {
      this.requests = data.requests;
    }
  },
  methods: {
    async respond(id, status) {
      const res = await fetch(`http://localhost:8080/api/remark-request/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      });
      const data = await res.json();
      if (data.success) {
        alert(`Marked as ${status}`);
        this.requests = this.requests.map(req =>
          req.id === id ? { ...req, status } : req
        );
      }
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('role');
      localStorage.removeItem('user_id');
      this.$router.push('/lecturer/login');
    }
  }
}
</script>

<style scoped>
/* Layout */
.app-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
  margin: 0;
}

/* Sidebar */
.sidebar {
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
}

.logo {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 2rem;
  text-align: center;
}

.nav-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-links li {
  padding: 0.8rem 1rem;
  margin-bottom: 0.5rem;
  background-color: #3749a3;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.nav-links li:hover {
  background-color: #475aca;
}

/* Main Content */
.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* Navbar */
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
  border: none;
  color: white;
  padding: 0.4rem 0.8rem;
  margin-left: 1rem;
  border-radius: 5px;
  cursor: pointer;
}

.navbar button:hover {
  background-color: #dc2626;
}

/* Dashboard Section */
.dashboard {
  padding: 2rem;
}

.dashboard h1 {
  font-size: 1.8rem;
  color: #1e3a8a;
  margin-bottom: 1.5rem;
  text-align: center;
}

/* Requests Table */
.requests-table {
  width: 100%;
  border-collapse: collapse;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
}

.requests-table th,
.requests-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.requests-table th {
  background-color: #e0e7ff;
  color: #1e3a8a;
}

.status-tag {
  font-weight: bold;
  padding: 0.2rem 0.6rem;
  border-radius: 5px;
  text-transform: capitalize;
}

.status-tag.pending {
  background-color: #fff4cc;
  color: #b45309;
}

.status-tag.approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-tag.rejected {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Buttons */
.btn {
  padding: 0.4rem 0.8rem;
  margin-right: 0.4rem;
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn.green {
  background-color: #28a745;
}

.btn.green:hover {
  background-color: #218838;
}

.btn.red {
  background-color: #dc3545;
}

.btn.red:hover {
  background-color: #c82333;
}

.no-course {
  margin-top: 2rem;
  text-align: center;
  color: #888;
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
