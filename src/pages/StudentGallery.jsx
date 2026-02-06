import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const StudentGallery = () => {
    const [events, setEvents] = useState([]);
    const [filteredEvents, setFilteredEvents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    // const [statusFilter, setStatusFilter] = useState('all'); // Removed

    const user = JSON.parse(localStorage.getItem('user'));
    const navigate = useNavigate();
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchEvents();
    }, []);

    useEffect(() => {
        // Filter: Only show CLOSED events + Search
        let result = events.filter(e => e.status === 'closed');

        if (searchTerm) {
            result = result.filter(e =>
                e.event_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                e.description?.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }

        setFilteredEvents(result);
    }, [events, searchTerm]);

    const fetchEvents = async () => {
        try {
            const response = await api.get(`/student/events.php?user_id=${user.id}`);
            setEvents(response.data);
            // Initial filter will happen in useEffect
        } catch (error) {
            console.error('Error fetching events:', error);
            showNotification('Failed to fetch events', 'error');
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div className="text-center py-20">Loading highlights...</div>;

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-500">
            <div className="text-center mb-8">
                <h1 className="text-4xl font-extrabold text-gray-800 mb-4">✨ Event Highlights</h1>
                <p className="text-xl text-gray-500 max-w-2xl mx-auto">Explore memories, photos, and details from our past campus events.</p>
            </div>

            {/* Controls Section */}
            <div className="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 justify-between items-center sticky top-4 z-10 backdrop-blur-md bg-white/90">
                <div className="relative w-full">
                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                    <input
                        type="text"
                        placeholder="Search highlights..."
                        className="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {filteredEvents.length === 0 ? (
                <div className="text-center py-20 bg-white rounded-3xl shadow-sm border border-dashed border-gray-200">
                    <div className="text-6xl mb-4">🔍</div>
                    <h3 className="text-xl font-bold text-gray-800">No events found</h3>
                    <p className="text-gray-400 mt-2">Try adjusting your search or filters.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {filteredEvents.map((event) => (
                        <div
                            key={event.event_id}
                            onClick={() => navigate(`/student/event/${event.event_id}`)}
                            className="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 cursor-pointer group hover:shadow-2xl hover:-translate-y-2 transition-all duration-300"
                        >
                            <div className="h-56 overflow-hidden relative">
                                {event.image_url ? (
                                    <img
                                        src={`${BASE_URL}/${event.image_url}`}
                                        alt={event.event_name}
                                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                    />
                                ) : (
                                    <div className="w-full h-full bg-gray-100 flex items-center justify-center text-gray-300 text-5xl">
                                        🖼️
                                    </div>
                                )}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                                    <span className="text-white font-bold flex items-center gap-2">
                                        <span>View Photos</span>
                                        <svg className="w-5 h-5 translate-x-0 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                    </span>
                                </div>
                            </div>
                            <div className="p-6">
                                <h3 className="text-xl font-bold text-gray-800 mb-2 truncate group-hover:text-blue-600 transition-colors">
                                    {event.event_name}
                                </h3>
                                <div className="flex justify-between items-center text-sm text-gray-500 mb-4">
                                    <span className="flex items-center gap-1">
                                        📅 {new Date(event.event_date).toLocaleDateString()}
                                    </span>
                                    <span className={`px-2 py-0.5 rounded-full text-xs font-bold uppercase ${event.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
                                        {event.status}
                                    </span>
                                </div>
                                <p className="text-gray-600 text-sm line-clamp-2 leading-relaxed">
                                    {event.description}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default StudentGallery;
