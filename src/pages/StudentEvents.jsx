import React, { useEffect, useState } from 'react';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const StudentEvents = () => {
    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState({});
    const [pageLoading, setPageLoading] = useState(true);
    const user = JSON.parse(localStorage.getItem('user'));
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        setPageLoading(true);
        try {
            const response = await api.get(`/student/events.php?user_id=${user.id}`);
            setEvents(response.data);
        } catch (error) {
            console.error('Error fetching events:', error);
            showNotification('Failed to fetch events', 'error');
        } finally {
            setPageLoading(false);
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
            fetchEvents(); // Refresh to update registration status
        } catch (error) {
            const msg = error.response?.data?.message || 'Registration failed';
            showNotification(msg, 'error');
        } finally {
            setLoading(prev => ({ ...prev, [event_id]: false }));
        }
    };

    if (pageLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-500">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800">All Events</h1>
                    <p className="text-gray-500 mt-1">Explore all the events available in our college.</p>
                </div>
                <div className="bg-blue-50 px-4 py-2 rounded-xl border border-blue-100 flex items-center gap-2">
                    <span className="text-blue-600 font-bold">{events.length}</span>
                    <span className="text-blue-700 text-sm font-medium">Events Found</span>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {events.map((event) => (
                    <div key={event.event_id} className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 flex flex-col hover:border-blue-200 transition-all group group-hover:shadow-md">
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
                            <div className={`absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-bold shadow-sm backdrop-blur-md ${event.status === 'open' ? 'bg-green-100/90 text-green-700' : 'bg-red-100/90 text-red-700'
                                }`}>
                                {event.status.toUpperCase()}
                            </div>
                            {parseInt(event.is_registered) > 0 && (
                                <div className="absolute top-4 left-4 bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-lg">
                                    Registered ✓
                                </div>
                            )}
                        </div>
                        <div className="p-6 flex-grow flex flex-col">
                            <h3 className="text-xl font-bold text-gray-800 mb-2 truncate" title={event.event_name}>
                                {event.event_name}
                            </h3>
                            <div className="text-sm text-gray-500 mb-4 space-y-2">
                                <p className="flex items-center font-medium">
                                    <span className="mr-2 opacity-70">🕒</span> {new Date(event.event_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                                </p>
                                <p className="flex items-center">
                                    <span className="mr-2 opacity-70">📍</span> {event.venue}
                                </p>
                            </div>
                            {event.description && (
                                <p className="text-gray-600 mb-6 text-sm line-clamp-2 leading-relaxed italic">
                                    "{event.description}"
                                </p>
                            )}
                            <div className="mt-auto pt-4 flex gap-3">
                                <button
                                    onClick={() => handleRegister(event.event_id)}
                                    disabled={loading[event.event_id] || parseInt(event.is_registered) > 0 || event.status !== 'open'}
                                    className={`flex-1 font-bold py-2.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center shadow-sm ${parseInt(event.is_registered) > 0
                                            ? 'bg-blue-50 text-blue-700 cursor-default border border-blue-100'
                                            : event.status !== 'open'
                                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                : 'bg-blue-600 hover:bg-blue-700 text-white hover:shadow-lg active:scale-95'
                                        } ${loading[event.event_id] ? 'opacity-70 cursor-wait' : ''}`}
                                >
                                    {loading[event.event_id] ? (
                                        <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    ) : (
                                        parseInt(event.is_registered) > 0 ? 'Already Registered' :
                                            event.status !== 'open' ? 'Registration Closed' : 'Register Now'
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {events.length === 0 && (
                <div className="py-20 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                    <div className="text-6xl mb-4">🏜️</div>
                    <h3 className="text-xl font-bold text-gray-800">No events found</h3>
                    <p className="text-gray-500 mt-2">Check back later for new event registrations!</p>
                </div>
            )}
        </div>
    );
};

export default StudentEvents;
