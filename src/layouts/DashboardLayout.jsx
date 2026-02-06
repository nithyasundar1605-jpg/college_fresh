import React from 'react';
import { Outlet } from 'react-router-dom';
import Navbar from '../components/Navbar';
import Sidebar from '../components/Sidebar';
import { useNotification } from '../context/NotificationContext';
import Message from '../components/Message';
import Chatbot from '../components/Chatbot';

const DashboardLayout = () => {
    const { notification, closeNotification } = useNotification();
    const user = JSON.parse(localStorage.getItem('user'));

    return (
        <div className="flex flex-col h-screen overflow-hidden">
            <Navbar />
            <div className="flex flex-1 overflow-hidden">
                <Sidebar />
                <div className="flex-1 overflow-y-auto p-6 bg-gray-50 custom-scrollbar">
                    {notification && (
                        <Message
                            message={notification.message}
                            type={notification.type}
                            onClose={closeNotification}
                        />
                    )}
                    <div className="max-w-7xl mx-auto">
                        <Outlet />
                    </div>
                </div>
            </div>
            {user && user.role !== 'admin' && <Chatbot />}
        </div>
    );
};

export default DashboardLayout;
