<template>
  <div class="enroll-view">
    <div v-if="enrolledCourses.length > 0" class="enrolled-section">
      <h2 class="subtitle">✅ Enrolled Courses</h2>
      <ul class="course-list">
        <li
          v-for="course in enrolledCourses"
          :key="course.id"
          class="course-item"
        >
          <div class="course-info">
            <p class="course-name">{{ course.code }} - {{ course.name }}</p>
            <p class="course-instructor">
              Instructor: {{ course.instructor || "TBA" }}
            </p>
          </div>
          <button class="enrolled-button" disabled>Enrolled</button>
        </li>
      </ul>
    </div>
    <h1 class="title">📚 Enroll in Available Courses</h1>

    <div v-if="availableCourses.length === 0" class="no-courses">
      No courses available for enrollment at the moment.
    </div>

    <ul class="course-list">
      <li
        v-for="course in availableCourses"
        :key="course.id"
        class="course-item"
      >
        <div class="course-info">
          <p class="course-name">{{ course.code }} - {{ course.name }}</p>
          <p class="course-instructor">
            Instructor: {{ course.instructor || "TBA" }}
          </p>
        </div>
        <button
          v-if="!isEnrolled(course.id)"
          @click="enroll(course.id)"
          class="enroll-button"
        >
          Enroll
        </button>
        <button v-else disabled class="enrolled-button">Enrolled</button>
      </li>
    </ul>

    <p class="success-message" v-if="message">{{ message }}</p>
  </div>
</template>

<script>
export default {
  props: ["studentId"],
  data() {
    return {
      availableCourses: [],
      enrolledCourses: [],
      message: "",
    };
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch("http://localhost:8080/api/courses/simple");
        const data = await res.json();
        if (data.success) {
          const enrolledIds = this.enrolledCourses.map((c) => c.id);
          this.availableCourses = data.courses.filter(
            (c) => !enrolledIds.includes(c.id)
          );
        }
      } catch (err) {
        console.error("Error fetching courses:", err);
      }
    },
    async fetchEnrolledCourses() {
      try {
        const res = await fetch(
          `http://localhost:8080/api/student/${this.studentId}/enrolled-courses`
        );
        const data = await res.json();
        if (data.success) {
          this.enrolledCourses = data.courses;
        }
      } catch (err) {
        console.error("Error fetching enrolled courses:", err);
      }
    },
    async enroll(courseId) {
      try {
        const res = await fetch("http://localhost:8080/api/student/enroll", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            student_id: this.studentId,
            course_id: courseId,
          }),
        });
        const data = await res.json();
        this.message = data.message;

        if (data.success) {
          // 找到课程对象
          const enrolledCourse = this.availableCourses.find(
            (c) => c.id === courseId
          );

          if (enrolledCourse) {
            // 添加到已报名列表
            this.enrolledCourses.push(enrolledCourse);

            // 从可用课程中移除
            this.availableCourses = this.availableCourses.filter(
              (c) => c.id !== courseId
            );
          }
        }
      } catch (err) {
        console.error("Error enrolling:", err);
      }
    },
    isEnrolled(courseId) {
      return this.enrolledCourses.some((c) => c.id === courseId);
    },
  },
  mounted() {
    this.fetchEnrolledCourses().then(() => {
      this.fetchCourses();
    });
  },
};
</script>

<style scoped>
.enroll-view {
  padding: 2rem;
}

.title {
  font-size: 24px;
  font-weight: bold;
  color: #1e3a8a;
  margin-bottom: 1rem;
}

.no-courses {
  color: #666;
  font-style: italic;
}

.course-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.course-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #ffffff;
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.3s ease;
}

.course-item:hover {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.course-info {
  display: flex;
  flex-direction: column;
}

.course-name {
  font-weight: 600;
  font-size: 16px;
  color: #333;
}

.course-instructor {
  font-size: 14px;
  color: #888;
}

.enroll-button {
  background-color: #16a34a;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.enroll-button:hover {
  background-color: #15803d;
}

.success-message {
  margin-top: 1rem;
  color: #16a34a;
  font-weight: 500;
}

.subtitle {
  font-size: 20px;
  font-weight: 600;
  color: #065f46;
  margin-bottom: 0.75rem;
  margin-top: 1.5rem;
}

.enrolled-button {
  background-color: #d1d5db; /* Gray */
  color: #444;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  font-size: 14px;
  cursor: not-allowed;
}
</style>
