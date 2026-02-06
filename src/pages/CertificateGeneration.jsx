import React, { useEffect, useState } from 'react';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const CertificateGeneration = () => {
    const [events, setEvents] = useState([]);
    const [selectedEventId, setSelectedEventId] = useState('');
    const [registrations, setRegistrations] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedTypes, setSelectedTypes] = useState({});
    const { showNotification } = useNotification();
    const [generating, setGenerating] = useState({});

    const certificateTypes = [
        'First Prize',
        'Second Prize',
        'Third Prize',
        'Participation'
    ];

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await api.get('/admin/events.php');
            setEvents(response.data);
        } catch (error) {
            showNotification('Failed to fetch events', 'error');
        }
    };

    const fetchRegistrations = async (eventId) => {
        if (!eventId) {
            setRegistrations([]);
            return;
        }
        setLoading(true);
        try {
            const response = await api.get(`/admin/registrations.php?event_id=${eventId}`);
            setRegistrations(response.data);

            // Initialize types
            const newTypes = {};
            response.data.forEach(reg => {
                newTypes[reg.reg_id] = 'Participation';
            });
            setSelectedTypes(prev => ({ ...newTypes, ...prev }));
        } catch (error) {
            showNotification('Failed to fetch registrations', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleEventChange = (e) => {
        const eventId = e.target.value;
        setSelectedEventId(eventId);
        fetchRegistrations(eventId);
    };

    const handleTypeChange = (reg_id, type) => {
        setSelectedTypes(prev => ({ ...prev, [reg_id]: type }));
    };

    const handleGenerate = async (reg) => {
        const type = selectedTypes[reg.reg_id] || 'Participation';
        console.log('Generating for registration:', reg, 'Type:', type);
        setGenerating(prev => ({ ...prev, [reg.reg_id]: true }));
        try {
            await api.post('/admin/generate_certificate.php', {
                student_id: reg.user_id,
                event_id: selectedEventId,
                certificate_type: type
            });
            showNotification(`${type} certificate generated successfully!`, 'success');
            fetchRegistrations(selectedEventId); // Refresh list to update button state
        } catch (error) {
            const msg = error.response?.data?.message || 'Generation failed';
            showNotification(msg, 'error');
        } finally {
            setGenerating(prev => ({ ...prev, [reg.reg_id]: false }));
        }
    };

    return (
        <div className="max-w-6xl mx-auto">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-800">Certificate Generation</h1>
                <p className="text-gray-600 mt-2">Generate official certificates for students who participated in events.</p>
            </div>

            <div className="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8 max-w-md">
                <label className="block text-gray-700 font-semibold mb-2">Select Event</label>
                <select
                    className="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all outline-none"
                    value={selectedEventId}
                    onChange={handleEventChange}
                >
                    <option value="">-- Select an Event --</option>
                    {events.map((event) => (
                        <option key={event.event_id} value={event.event_id}>
                            {event.event_name} ({event.event_date})
                        </option>
                    ))}
                </select>
            </div>

            {selectedEventId && (
                <div className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div className="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 className="text-xl font-bold text-gray-800">Student List</h2>
                        <span className="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                            {registrations.length} Registered
                        </span>
                    </div>

                    {loading ? (
                        <div className="p-20 text-center">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p className="mt-4 text-gray-500">Loading registrations...</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead className="bg-gray-50 text-gray-500 uppercase text-xs font-bold">
                                    <tr>
                                        <th className="px-6 py-4">Student Name</th>
                                        <th className="px-6 py-4">Status</th>
                                        <th className="px-6 py-4">Certificate Type</th>
                                        <th className="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {registrations.map((reg) => (
                                        <tr key={reg.reg_id} className="hover:bg-gray-50 transition-colors">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    {reg.profile_pic ? (
                                                        <img
                                                            src={`${BASE_URL.replace('/backend', '')}/${reg.profile_pic}?t=${Date.now()}`}
                                                            alt={reg.student_name}
                                                            className="w-8 h-8 rounded-lg object-cover border border-gray-100"
                                                        />
                                                    ) : (
                                                        <div className="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px]">
                                                            {reg.student_name.charAt(0).toUpperCase()}
                                                        </div>
                                                    )}
                                                    <span className="font-medium text-gray-800">{reg.student_name}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 lowercase">
                                                <span className={`px-2 py-1 rounded-full text-xs font-bold ${reg.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'
                                                    }`}>
                                                    {reg.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                {parseInt(reg.is_generated) === 0 && reg.status === 'approved' ? (
                                                    <select
                                                        value={selectedTypes[reg.reg_id] || 'Participation'}
                                                        onChange={(e) => handleTypeChange(reg.reg_id, e.target.value)}
                                                        className="text-sm border border-gray-300 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500"
                                                    >
                                                        {certificateTypes.map(type => (
                                                            <option key={type} value={type}>{type}</option>
                                                        ))}
                                                    </select>
                                                ) : (
                                                    <span className={`px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${reg.certificate_type === 'First Prize' ? 'bg-yellow-100 text-yellow-700' :
                                                        reg.certificate_type === 'Second Prize' ? 'bg-gray-100 text-gray-700' :
                                                            reg.certificate_type === 'Third Prize' ? 'bg-orange-100 text-orange-700' :
                                                                parseInt(reg.is_generated) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-50 text-gray-400'
                                                        }`}>
                                                        {reg.certificate_type || (parseInt(reg.is_generated) > 0 ? 'Generated' : '-')}
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                {parseInt(reg.is_generated) > 0 ? (
                                                    <a
                                                        href={`${BASE_URL}/student/download_certificate.php?id=${reg.certificate_id}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="px-4 py-2 rounded-lg text-sm font-bold bg-green-600 hover:bg-green-700 text-white shadow-md active:transform active:scale-95 transition-all inline-flex items-center gap-2"
                                                    >
                                                        <span>Open Cert</span>
                                                        <span>📄</span>
                                                    </a>
                                                ) : (
                                                    <button
                                                        onClick={() => handleGenerate(reg)}
                                                        disabled={generating[reg.reg_id] || reg.status !== 'approved'}
                                                        className={`px-4 py-2 rounded-lg text-sm font-bold transition-all ${reg.status !== 'approved'
                                                            ? 'bg-gray-50 text-gray-300 cursor-not-allowed border border-gray-100'
                                                            : 'bg-blue-600 hover:bg-blue-700 text-white shadow-md active:transform active:scale-95'
                                                            } ${generating[reg.reg_id] ? 'opacity-70' : ''}`}
                                                    >
                                                        {generating[reg.reg_id] ? 'Generating...' : 'Generate Certificate'}
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                    {registrations.length === 0 && (
                                        <tr>
                                            <td colSpan="3" className="px-6 py-10 text-center text-gray-400">
                                                No students registered for this event yet.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default CertificateGeneration;
