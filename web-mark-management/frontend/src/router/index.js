import { createRouter, createWebHistory } from 'vue-router'
import AdminLogin from '../views/admin/AdminLogin.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminAddCourse from '../views/admin/AdminAddCourse.vue'
import AssignCourse from '../views/admin/AssignCourse.vue'
import AddUser from '../views/admin/AddUser.vue'
import LecturerLogin from '../views/lecturer/LecturerLogin.vue';
import StudentLogin from '../views/students/StudentLogin.vue';
import LecturerDashboard from '../views/lecturer/LecturerDashboard.vue';
import LecturerCourses from '../views/lecturer/LecturerCourses.vue';
import LecturerCourseView from '../views/lecturer/LecturerCourseView.vue';
import StudentDashboard from '../views/students/StudentDashboard.vue';
import CourseDetail from '../views/lecturer/CourseDetail.vue';

const routes = [
  {
    path: '/admin/login',
    component: AdminLogin
  },
  {
    path: '/admin/dashboard',
    component: AdminDashboard
  },
  { path: '/admin/courses/add', component: AdminAddCourse },
  { path: '/admin/courses/assign', component: AssignCourse },
  { path: '/admin/users/add', component: AddUser },
  { path: '/lecturer/login', component: LecturerLogin },
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/lecturer/course-detail/:id', component: CourseDetail },
  { path: '/lecturer/my-courses', component: LecturerCourses },
  { path: '/lecturer/course/:id', component: LecturerCourseView },
  { path: '/student/login', component: StudentLogin },
  { path: '/student/dashboard', component: StudentDashboard },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
