import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const StudentDashboard = () => {
    const [events, setEvents] = useState([]);
    const [registrations, setRegistrations] = useState([]);
    const [loading, setLoading] = useState({});
    const user = JSON.parse(localStorage.getItem('user'));
    const navigate = useNavigate();
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            const [eventsRes, regsRes] = await Promise.all([
                api.get(`/student/events.php?user_id=${user.id}`),
                api.get(`/student/my_registrations.php?user_id=${user.id}`)
            ]);
            setEvents(eventsRes.data);
            setRegistrations(regsRes.data);
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            showNotification('Failed to fetch dashboard data', 'error');
        }
    };

    const handleRegister = async (event_id) => {
        setLoading(prev => ({ ...prev, [event_id]: true }));
        try {
            await api.post('/student/register_event.php', {
                user_id: user.id,
                event_id: event_id
            });
            showNotification('Successfully registered for the event', 'success');
            fetchDashboardData(); // Refresh both sections
        } catch (error) {
            const msg = error.response?.data?.message || 'Registration failed';
            showNotification(msg, 'error');
        } finally {
            setLoading(prev => ({ ...prev, [event_id]: false }));
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
            <div className="max-w-5xl mx-auto space-y-8">

                {/* 👋 Welcome Section */}
                <div className="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 transition-all hover:shadow-md">
                    <h1 className="text-4xl font-black text-gray-900 tracking-tight">
                        Welcome, <span className="text-blue-600">{user.name}</span>! 👋
                    </h1>
                    <p className="mt-2 text-lg text-gray-600 font-medium">
                        Your personalized event hub. Here's a quick look at upcoming events and your registrations.
                    </p>
                </div>

                {/* 📅 Upcoming Events Section (Vertical Stack) */}
                <div className="space-y-6">
                    <div className="flex items-center justify-between">
                        <h2 className="text-2xl font-bold text-gray-800 flex items-center">
                            <span className="mr-3 text-3xl">📅</span> Upcoming Events
                        </h2>
                        <button
                            onClick={() => navigate('/student/events')}
                            className="text-blue-600 hover:text-blue-800 font-bold text-sm"
                        >
                            View All Events →
                        </button>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {events.slice(0, 3).map((event) => (
                            <div key={event.event_id} className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 flex flex-col hover:border-blue-200 transition-all group">
                                <div className="h-48 overflow-hidden relative bg-gray-100">
                                    {event.image_url ? (
                                        <img
                                            src={`${BASE_URL}/${event.image_url}`}
                                            alt={event.event_name}
                                            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />
                                    ) : (
                                        <div className="w-full h-full flex items-center justify-center text-gray-300 text-5xl">
                                            🖼️
                                        </div>
                                    )}
                                    <div className="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-blue-600 shadow-sm">
                                        {event.status.toUpperCase()}
                                    </div>
                                </div>
                                <div className="p-6 flex-grow flex flex-col">
                                    <h3 className="text-xl font-bold text-gray-800 mb-2 truncate" title={event.event_name}>
                                        {event.event_name}
                                    </h3>
                                    <div className="text-sm text-gray-500 mb-4 space-y-2">
                                        <p className="flex items-center font-medium italic">
                                            <span className="mr-2 opacity-70">🕒</span> {new Date(event.event_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                                        </p>
                                        <p className="flex items-center">
                                            <span className="mr-2 opacity-70">📍</span> {event.venue}
                                        </p>
                                    </div>
                                    <div className="mt-auto pt-4 flex gap-3">
                                        <button
                                            onClick={() => handleRegister(event.event_id)}
                                            disabled={loading[event.event_id] || parseInt(event.is_registered) > 0}
                                            className={`flex-1 font-bold py-2.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center shadow-sm ${parseInt(event.is_registered) > 0
                                                ? 'bg-green-50 text-green-700 cursor-default border border-green-100'
                                                : 'bg-blue-600 hover:bg-blue-700 text-white hover:shadow-lg active:scale-95'
                                                } ${loading[event.event_id] ? 'opacity-70 cursor-wait' : ''}`}
                                        >
                                            {loading[event.event_id] ? (
                                                <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            ) : (
                                                parseInt(event.is_registered) > 0 ? '✓ Registered' : 'Register Now'
                                            )}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* 📝 My Registrations Section (Below Events) */}
                <div className="space-y-6">
                    <h2 className="text-2xl font-bold text-gray-800 flex items-center">
                        <span className="mr-3 text-3xl">📝</span> My Registrations
                    </h2>
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="divide-y divide-gray-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                            {registrations.length > 0 ? (
                                registrations.map((reg) => (
                                    <div key={reg.reg_id} className="p-5 hover:bg-gray-50 transition-colors border-r border-b last:border-r-0">
                                        <div className="flex justify-between items-start mb-1 gap-2">
                                            <h4 className="font-bold text-gray-800 text-sm truncate">{reg.event_name}</h4>
                                            <span className={`flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider ${reg.status === 'approved' ? 'bg-green-100 text-green-700' :
                                                reg.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                                    'bg-blue-100 text-blue-700'
                                                }`}>
                                                {reg.status}
                                            </span>
                                        </div>
                                        <p className="text-[11px] text-gray-500 font-medium">
                                            {new Date(reg.event_date).toLocaleDateString()} • {reg.venue}
                                        </p>
                                    </div>
                                ))
                            ) : (
                                <div className="p-8 text-center text-gray-400 col-span-full">
                                    <p className="text-sm">No registrations yet.</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default StudentDashboard;
