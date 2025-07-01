<template>
  <div class="grades-view">
    <h2 class="title">🎓 My Grades</h2>

    <ul class="course-list">
      <li v-for="course in myCourses" :key="course.id" class="course-card">
        <div class="course-header">
          <div>
            <strong class="course-code">{{ course.code }}</strong> -
            {{ course.name }}
          </div>
        </div>

        <div class="course-actions">
          <div class="remark-status-wrapper">
            <!-- 如果没有申请或被拒绝，可以显示按钮 -->
            <button
              v-if="
                !course.remark_status
              "
              class="remark-button"
              @click="requestRemark(course.id)"
            >
              Request Remark
            </button>

            <!-- 如果已经申请，显示状态标签 -->
            <span
              v-else
              :class="['status-label', getStatusClass(course.remark_status)]"
            >
              {{ getStatusText(course.remark_status) }}
            </span>
          </div>

          <button class="view-button" @click="selectCourse(course)">
            View Final Result
          </button>
        </div>
      </li>
    </ul>

    <!-- 弹出 FinalResult Modal -->
    <Modal v-if="selectedCourse" @close="selectedCourse = null">
      <FinalResult :studentId="studentId" :courseId="selectedCourse.id" />
    </Modal>
  </div>
</template>

<script>
import FinalResult from "./FinalResult.vue";
import Modal from "./ModalDialog.vue"; // ✅ 引入 Modal

export default {
  components: {
    FinalResult,
    Modal,
  },
  data() {
    return {
      studentId: localStorage.getItem("user_id"),
      myCourses: [],
      selectedCourse: null,
    };
  },
  methods: {
    async loadMyCourses() {
      const res = await fetch(
        `http://localhost:8080/api/student/${this.studentId}/courses`
      );
      const data = await res.json();
      if (data.success) {
        this.myCourses = data.courses;
      }
    },
    selectCourse(course) {
      this.selectedCourse = null;
      this.$nextTick(() => {
        this.selectedCourse = course;
      });
    },
    getStatusClass(status) {
      switch (status) {
        case "pending":
          return "status-pending";
        case "approved":
          return "status-approved";
        case "rejected":
          return "status-rejected";
        default:
          return "";
      }
    },
    async requestRemark(courseId) {
      const res = await fetch("http://localhost:8080/api/remark-request", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          student_id: this.studentId,
          course_id: courseId,
        }),
      });
      const data = await res.json();
      if (data.success) {
        alert("Request submitted.");
        this.loadMyCourses();
      } else {
        alert(data.message || "Failed to submit.");
      }
    },
    getStatusText(status) {
      if (status === "approved") return "Done Remark";
      return status;
    },
  },
  mounted() {
    this.loadMyCourses();
  },
};
</script>

<style scoped>
.grades-view {
  padding: 2rem;
  background-color: #f5f6fa;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.title {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 2rem;
  color: #1e3a8a;
}

.course-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.course-card {
  background-color: #ffffff;
  padding: 1rem 1.2rem;
  margin-bottom: 1.5rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.course-code {
  color: #1e3a8a;
}

.status-label {
  padding: 0.3rem 0.6rem;
  font-size: 0.85rem;
  border-radius: 4px;
  font-weight: 600;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-rejected {
  background-color: #fee2e2;
  color: #991b1b;
}

.course-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.remark-button {
  background-color: #f59e0b;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
}

.remark-button:hover {
  background-color: #d97706;
}

.remark-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.view-link {
  background: none;
  border: none;
  color: #2563eb;
  text-decoration: underline;
  cursor: pointer;
  font-weight: 500;
}

.view-link:hover {
  color: #1d4ed8;
}

.final-result {
  margin-top: 2rem;
}

.view-button {
  background-color: #3b82f6; /* 蓝色 */
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
}

.view-button:hover {
  background-color: #2563eb;
}
</style>
