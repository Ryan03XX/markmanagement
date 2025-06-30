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
import DeleteUser from '../views/admin/UserList.vue'
import LecturerCourseRanking from '../views/lecturer/LecturerCourseRanking.vue';
import StudentRankingView from '../views/students/RankingView.vue';
import StudentGradeView from '../views/students/GradeView.vue';
import StudentEnrollView from '../views/students/EnrollView.vue';
import RemarkRequests from '../views/lecturer/RemarkRequests.vue';

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
  { path: '/admin/users/manage', component: DeleteUser },
  { path: '/lecturer/login', component: LecturerLogin },
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/lecturer/course-detail/:id', component: CourseDetail },
  { path: '/lecturer/my-courses', component: LecturerCourses },
  { path: '/lecturer/course/:id', component: LecturerCourseView },
  { path: '/', component: StudentLogin },
  { path: '/student/login', component: StudentLogin },
  { path: '/student/dashboard', component: StudentDashboard },
  { path: '/lecturer/course-ranking/:id', component: LecturerCourseRanking },
  { path: '/student/ranking', component: StudentRankingView },
  { path: '/student/grades', component: StudentGradeView },
  { path: '/student/enroll', component: StudentEnrollView },
  { path: '/lecturer/remark-requests', component: RemarkRequests },
  {
    path: '/forgot-password',
    component: () => import('@/views/ForgotPassword.vue')
  },
  {
    path: '/reset-password',
    component: () => import('@/views/ResetPassword.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
