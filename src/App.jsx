import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Register from './pages/Register';
import DashboardLayout from './layouts/DashboardLayout';
import AdminDashboard from './pages/AdminDashboard';
import ManageEvents from './pages/ManageEvents';
import Registrations from './pages/Registrations';
import StudentDashboard from './pages/StudentDashboard';
import EventDetails from './pages/EventDetails';
import MyRegistrations from './pages/MyRegistrations';
import MyCertificates from './pages/MyCertificates';
import CertificateGeneration from './pages/CertificateGeneration';
import AdminGallery from './pages/AdminGallery';
import AdminEventReport from './pages/AdminEventReport';
import Reports from './pages/Reports';
import Profile from './pages/Profile';
import StudentEvents from './pages/StudentEvents';
import Settings from './pages/Settings';
import StudentList from './pages/StudentList';
import StudentDetails from './pages/StudentDetails';
import StudentGallery from './pages/StudentGallery';
import EventCalendar from './pages/EventCalendar';
import { ThemeProvider } from './context/ThemeContext';

// Private Route Wrapper
const PrivateRoute = ({ children, role }) => {
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user) return <Navigate to="/login" />;
    if (role && user.role !== role) return <Navigate to="/login" />;
    return children;
};

function App() {
    return (
        <ThemeProvider>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/register" element={<Register />} />
                <Route path="/" element={<Navigate to="/login" />} />

                {/* Admin Routes */}
                <Route path="/admin" element={<PrivateRoute role="admin"><DashboardLayout /></PrivateRoute>}>
                    <Route path="dashboard" element={<AdminDashboard />} />
                    <Route path="events" element={<ManageEvents />} />
                    <Route path="students" element={<StudentList />} />
                    <Route path="student/:id" element={<StudentDetails />} />
                    <Route path="registrations" element={<Registrations />} />
                    <Route path="certificate-generation" element={<CertificateGeneration />} />
                    <Route path="gallery/:eventId" element={<AdminGallery />} />
                    <Route path="report/:eventId" element={<AdminEventReport />} />
                    <Route path="reports" element={<Reports />} />
                    <Route path="settings" element={<Settings />} />
                </Route>

                {/* Student Routes */}
                <Route path="/student" element={<PrivateRoute role="student"><DashboardLayout /></PrivateRoute>}>
                    <Route path="dashboard" element={<StudentDashboard />} />
                    <Route path="events" element={<StudentEvents />} />
                    <Route path="calendar" element={<EventCalendar />} />
                    <Route path="gallery" element={<StudentGallery />} />
                    <Route path="event/:id" element={<EventDetails />} />
                    <Route path="my-registrations" element={<MyRegistrations />} />
                    <Route path="my-certificates" element={<MyCertificates />} />
                    <Route path="profile" element={<Profile />} />
                    <Route path="settings" element={<Settings />} />
                </Route>
            </Routes>
        </ThemeProvider>
    );
}

export default App;
