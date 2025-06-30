<template>
  <div class="app-layout">
    <!-- 侧边栏 -->
    <aside class="sidebar">
      <div class="logo">🎓 Student Panel</div>
      <ul class="nav-links">
        <li
          @click="activeTab = 'dashboard'"
          :class="{ active: activeTab === 'dashboard' }"
        >
          🏠 Dashboard
        </li>
        <li
          @click="activeTab = 'enroll'"
          :class="{ active: activeTab === 'enroll' }"
        >
          📚 Enroll Courses
        </li>
        <li
          @click="activeTab = 'grades'"
          :class="{ active: activeTab === 'grades' }"
        >
          🎓 My Grades
        </li>
        <li
          @click="activeTab = 'ranking'"
          :class="{ active: activeTab === 'ranking' }"
        >
          🏆 Course Ranking
        </li>
      </ul>
      <div class="logout-section">
    <button @click="logout">🚪 Logout</button>
  </div>
    </aside>

    <!-- 主内容 -->
    <div class="main-content">
      <!-- 顶部导航 -->
      <header class="navbar">
        <div class="navbar-right">
          <span>Welcome, {{ studentName }}</span>
          <!-- <button @click="logout">Logout</button> -->
        </div>
        <!-- Notification Icon -->
        <div class="notification-wrapper" @click="toggleNotifications">
          <span class="bell">🔔</span>
          <span v-if="unreadCount > 0" class="dot"></span>
        </div>

        <!-- Notification Dropdown -->
        <div
          v-if="showNotifications"
          class="notification-dropdown"
          ref="dropdown"
        >
          <h4>📨 Notifications</h4>
          <ul>
            <li
              v-for="n in notifications"
              :key="n.id"
              :class="{ unread: !n.is_read }"
              @click="markAsRead(n)"
            >
              <strong v-if="!n.is_read">[NEW]</strong> {{ n.message }}
              <small>({{ formatDate(n.created_at) }})</small>
            </li>
          </ul>
        </div>
      </header>

      <!-- 内容 -->
      <main class="dashboard">
        <component :is="currentComponent" />
      </main>
    </div>
  </div>
</template>

<script>
import EnrollView from "./EnrollView.vue";
import GradeView from "./GradeView.vue";
import RankingView from "./RankingView.vue";
import StudentHome from "./StudentHome.vue";

export default {
  components: {
    EnrollView,
    GradeView,
    RankingView,
    StudentHome,
  },
  data() {
    return {
      activeTab: "dashboard",
      studentName: localStorage.getItem("name") || "Student",
      notifications: [],
      showNotifications: false,
    };
  },
  computed: {
    currentComponent() {
      switch (this.activeTab) {
        case "enroll":
          return "EnrollView";
        case "grades":
          return "GradeView";
        case "ranking":
          return "RankingView";
        case "dashboard":
        default:
          return "StudentHome";
      }
    },
    unreadCount() {
      return this.notifications.filter((n) => !n.is_read).length;
    },
  },
  async created() {
    await this.fetchNotifications();
  },
  methods: {
    logout() {
      localStorage.clear();
      this.$router.push("/student/login");
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString();
    },
    async fetchNotifications() {
      const studentId = localStorage.getItem("user_id");
      try {
        const res = await fetch(
          `http://localhost:8080/api/student/${studentId}/notifications`
        );
        const data = await res.json();
        if (data.success) {
          this.notifications = data.notifications;
        } else {
          console.error("Failed to fetch notifications:", data.message);
        }
      } catch (error) {
        console.error("Error fetching notifications:", error);
      }
    },
    async markAsRead(notification) {
      if (notification.is_read) return; // 已读就不再处理
      try {
        const res = await fetch(
          `http://localhost:8080/api/student/notification/${notification.id}/read`,
          {
            method: "POST",
          }
        );
        const data = await res.json();
        if (data.success) {
          notification.is_read = 1; // 本地更新为已读
        }
      } catch (error) {
        console.error("Error marking as read:", error);
      }
    },
    handleOutsideClick(event) {
      const dropdown = this.$refs.dropdown;
      const wrapper = this.$el.querySelector(".notification-wrapper");

      // 如果点击的不是 dropdown 和 icon，关闭 dropdown
      if (
        this.showNotifications &&
        dropdown &&
        !dropdown.contains(event.target) &&
        !wrapper.contains(event.target)
      ) {
        this.showNotifications = false;
      }
    },

    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
    },
  },
  mounted() {
  document.addEventListener("click", this.handleOutsideClick);
},
beforeUnmount() { // ✅ Vue 3 使用 beforeUnmount
  document.removeEventListener("click", this.handleOutsideClick);
},
};
</script>

<style scoped>
.app-layout {
  display: flex;
  height: 100vh; /* 固定整页高度 */
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
  overflow: hidden; /* 避免外部滚动 */
}

/* 侧边栏 */
.sidebar {
  width: 220px;
  background-color: #1e3a8a;
  color: white;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden; /* 顶部导航栏固定，不跟着滚动 */
}

.dashboard {
  flex-grow: 1;
  padding: 2rem;
  overflow-y: auto;
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

.nav-links li.active {
  background-color: #2563eb;
}

/* 主内容 */
.main-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* 顶部导航栏 */
.navbar {
  background-color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

.notification-wrapper {
  position: relative;
  margin-right: 1rem;
  cursor: pointer;
}

.bell {
  font-size: 1.5rem;
}

.dot {
  position: absolute;
  top: -4px;
  right: -4px;
  background-color: red;
  border-radius: 50%;
  width: 10px;
  height: 10px;
}

.notification-dropdown {
  position: absolute;
  top: 60px;
  right: 2rem;
  width: 300px;
  max-height: 400px;
  background: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow-y: auto;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  padding: 1rem;
  z-index: 1000;
}

.notification-dropdown h4 {
  margin-top: 0;
}

.notification-dropdown ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notification-dropdown li {
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
  cursor: pointer; /* ✅ 可点击光标 */
  transition: background-color 0.2s;
}

.notification-dropdown li:hover {
  background-color: #e0f2fe; /* hover 提示 */
}

.notification-dropdown li.unread {
  background-color: #f0f9ff;
  font-weight: 600;
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
