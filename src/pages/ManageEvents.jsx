import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const ManageEvents = () => {
    const { showNotification, showConfirm } = useNotification();
    const navigate = useNavigate();
    const [events, setEvents] = useState([]);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [currentEvent, setCurrentEvent] = useState({
        event_id: null, event_name: '', college_name: '', description: '', event_date: '', venue: '', status: 'open', image_url: ''
    });
    const [selectedImage, setSelectedImage] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [coordSignature, setCoordSignature] = useState(null);
    const [coordPreview, setCoordPreview] = useState(null);
    const [mgmtSignature, setMgmtSignature] = useState(null);
    const [mgmtPreview, setMgmtPreview] = useState(null);
    const [error, setError] = useState('');
    const user = JSON.parse(localStorage.getItem('user'));

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await api.get('/admin/events.php');
            setEvents(response.data);
        } catch (error) {
            console.error('Error fetching events:', error);
            showNotification('Error fetching events', 'error');
        }
    };

    const handleDelete = (id) => {
        showConfirm(
            'Delete Event',
            'Are you sure you want to delete this event? This action cannot be undone.',
            async () => {
                try {
                    await api.delete(`/admin/events.php?id=${id}`);
                    showNotification('Event deleted successfully', 'success');
                    fetchEvents();
                } catch (error) {
                    showNotification('Failed to delete event', 'error');
                }
            }
        );
    };

    const handleEdit = (event) => {
        setCurrentEvent(event);
        setCurrentEvent(event);
        setSelectedImage(null);
        setImagePreview(event.image_url ? `${BASE_URL}/${event.image_url}` : null);
        setCoordSignature(null);
        setCoordPreview(event.coordinator_signature ? `${BASE_URL}/${event.coordinator_signature}` : null);
        setMgmtSignature(null);
        setMgmtPreview(event.management_signature ? `${BASE_URL}/${event.management_signature}` : null);
        setIsModalOpen(true);
    };

    const handleAdd = () => {
        setCurrentEvent({ event_id: null, event_name: '', college_name: '', description: '', event_date: '', venue: '', status: 'open', image_url: '' });
        setSelectedImage(null);
        setImagePreview(null);
        setCoordSignature(null);
        setCoordPreview(null);
        setMgmtSignature(null);
        setMgmtPreview(null);
        setIsModalOpen(true);
    };

    const handleImageChange = (e, setFile, setPreview) => {
        const file = e.target.files[0];
        if (file) {
            setFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreview(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const formData = new FormData();
            formData.append('event_name', currentEvent.event_name);
            formData.append('college_name', currentEvent.college_name || '');
            formData.append('description', currentEvent.description);
            formData.append('event_date', currentEvent.event_date);
            formData.append('venue', currentEvent.venue);
            formData.append('status', currentEvent.status);
            formData.append('created_by', user?.id || 1);

            if (currentEvent.event_id) {
                formData.append('event_id', currentEvent.event_id);
                formData.append('existing_image_url', currentEvent.image_url || '');
                formData.append('existing_coordinator_signature', currentEvent.coordinator_signature || '');
                formData.append('existing_management_signature', currentEvent.management_signature || '');
            }

            if (selectedImage) {
                formData.append('image', selectedImage);
            }
            if (coordSignature) {
                formData.append('coordinator_signature', coordSignature);
            }
            if (mgmtSignature) {
                formData.append('management_signature', mgmtSignature);
            }

            // Always use POST because PUT with multipart is problematic in PHP
            await api.post('/admin/events.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            showNotification(currentEvent.event_id ? 'Event updated successfully' : 'Event created successfully', 'success');
            setIsModalOpen(false);
            fetchEvents();
        } catch (err) {
            console.error('Event submission error:', err);
            if (err.response && err.response.data && err.response.data.message) {
                setError(err.response.data.message);
            } else {
                setError(err.message || 'Operation failed');
            }
        }
    };

    return (
        <div>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Manage Events</h1>
                <button
                    onClick={handleAdd}
                    className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200"
                >
                    Add Event
                </button>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {events.map((event) => (
                            <tr key={event.event_id}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{event.event_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{event.event_date}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{event.venue}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${event.status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        }`}>
                                        {event.status}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button onClick={() => navigate(`/admin/report/${event.event_id}`)} className="text-orange-600 hover:text-orange-900">Report</button>
                                    <button onClick={() => navigate(`/admin/gallery/${event.event_id}`)} className="text-purple-600 hover:text-purple-900">Gallery</button>
                                    <button onClick={() => handleEdit(event)} className="text-blue-600 hover:text-blue-900">Edit</button>
                                    <button onClick={() => handleDelete(event.event_id)} className="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {isModalOpen && (
                <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center z-50 p-4">
                    <div className="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                        <div className="flex justify-between items-center mb-6">
                            <h2 className="text-2xl font-black text-gray-800">{currentEvent.event_id ? '✏️ Edit Event' : '✨ Add New Event'}</h2>
                            <button onClick={() => setIsModalOpen(false)} className="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                        </div>

                        {error && <div className="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-bold border border-red-100">{error}</div>}

                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Row 1: Basic Names */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Event Name</label>
                                    <input
                                        type="text"
                                        placeholder="e.g. Annual Tech Fest 2024"
                                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        value={currentEvent.event_name}
                                        onChange={(e) => setCurrentEvent({ ...currentEvent, event_name: e.target.value })}
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Display College Name</label>
                                    <input
                                        type="text"
                                        placeholder="e.g. CITY COLLEGE OF ENGINEERING"
                                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        value={currentEvent.college_name || ''}
                                        onChange={(e) => setCurrentEvent({ ...currentEvent, college_name: e.target.value })}
                                    />
                                </div>
                            </div>

                            {/* Row 2: Date, Status, Venue */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Event Date</label>
                                    <input
                                        type="date"
                                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        value={currentEvent.event_date}
                                        onChange={(e) => setCurrentEvent({ ...currentEvent, event_date: e.target.value })}
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Status</label>
                                    <select
                                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        value={currentEvent.status}
                                        onChange={(e) => setCurrentEvent({ ...currentEvent, status: e.target.value })}
                                    >
                                        <option value="open">🟢 Open for Registration</option>
                                        <option value="closed">🔴 Closed</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Venue</label>
                                    <input
                                        type="text"
                                        placeholder="e.g. Main Auditorium"
                                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        value={currentEvent.venue}
                                        onChange={(e) => setCurrentEvent({ ...currentEvent, venue: e.target.value })}
                                        required
                                    />
                                </div>
                            </div>

                            {/* Row 3: Description */}
                            <div>
                                <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Event Description</label>
                                <textarea
                                    rows="3"
                                    placeholder="Tell participants what this event is about..."
                                    className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                    value={currentEvent.description}
                                    onChange={(e) => setCurrentEvent({ ...currentEvent, description: e.target.value })}
                                ></textarea>
                            </div>

                            {/* Row 4: Visuals (Image + Signatures) */}
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-4 border-t border-gray-100">
                                {/* Image Upload */}
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Event Banner</label>
                                    <div className="relative group">
                                        <div className="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden transition-colors group-hover:border-blue-400">
                                            {imagePreview ? (
                                                <img src={imagePreview} alt="Preview" className="w-full h-full object-cover" />
                                            ) : (
                                                <div className="text-center">
                                                    <span className="text-3xl block">🖼️</span>
                                                    <span className="text-[10px] text-gray-400 font-bold">CLICK TO UPLOAD</span>
                                                </div>
                                            )}
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) => handleImageChange(e, setSelectedImage, setImagePreview)}
                                            className="absolute inset-0 opacity-0 cursor-pointer"
                                        />
                                    </div>
                                </div>

                                {/* Coordinator Signature */}
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Coordinator Sign</label>
                                    <div className="relative group">
                                        <div className="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden transition-colors group-hover:border-purple-400">
                                            {coordPreview ? (
                                                <img src={coordPreview} alt="Coord Sig" className="h-full object-contain p-2" />
                                            ) : (
                                                <div className="text-center">
                                                    <span className="text-3xl block">✍️</span>
                                                    <span className="text-[10px] text-gray-400 font-bold">COORDINATOR</span>
                                                </div>
                                            )}
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) => handleImageChange(e, setCoordSignature, setCoordPreview)}
                                            className="absolute inset-0 opacity-0 cursor-pointer"
                                        />
                                    </div>
                                </div>

                                {/* Management Signature */}
                                <div>
                                    <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Management Sign</label>
                                    <div className="relative group">
                                        <div className="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center overflow-hidden transition-colors group-hover:border-indigo-400">
                                            {mgmtPreview ? (
                                                <img src={mgmtPreview} alt="Mgmt Sig" className="h-full object-contain p-2" />
                                            ) : (
                                                <div className="text-center">
                                                    <span className="text-3xl block">🏛️</span>
                                                    <span className="text-[10px] text-gray-400 font-bold">MANAGEMENT</span>
                                                </div>
                                            )}
                                        </div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) => handleImageChange(e, setMgmtSignature, setMgmtPreview)}
                                            className="absolute inset-0 opacity-0 cursor-pointer"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button
                                    type="button"
                                    onClick={() => setIsModalOpen(false)}
                                    className="px-6 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition-all"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="px-8 py-2.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 active:scale-95 transition-all"
                                >
                                    {currentEvent.event_id ? 'Update Event' : 'Create Event'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ManageEvents;
