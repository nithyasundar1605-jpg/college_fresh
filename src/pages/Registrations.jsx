import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const Registrations = () => {
    const [registrations, setRegistrations] = useState([]);
    const [selectedTypes, setSelectedTypes] = useState({});
    const { showNotification } = useNotification();

    const certificateTypes = [
        'First Prize',
        'Second Prize',
        'Third Prize',
        'Participation'
    ];

    useEffect(() => {
        fetchRegistrations();
    }, []);

    const fetchRegistrations = async () => {
        try {
            const response = await api.get('/admin/registrations.php');
            setRegistrations(response.data);

            // Initialize types if not set
            const newTypes = {};
            response.data.forEach(reg => {
                newTypes[reg.reg_id] = 'Participation';
            });
            setSelectedTypes(prev => ({ ...newTypes, ...prev }));
        } catch (error) {
            console.error('Error fetching registrations:', error);
            showNotification('Failed to fetch registrations', 'error');
        }
    };

    const handleStatusUpdate = async (reg_id, status) => {
        try {
            await api.put('/admin/registrations.php', { reg_id, status });
            showNotification(`Status updated to ${status}`, 'success');
            fetchRegistrations();
        } catch (error) {
            showNotification('Failed to update status', 'error');
        }
    };

    const handleTypeChange = (reg_id, type) => {
        setSelectedTypes(prev => ({ ...prev, [reg_id]: type }));
    };

    const handleGenerateCertificate = async (reg) => {
        const type = selectedTypes[reg.reg_id] || 'Participation';
        try {
            await api.post('/admin/generate_certificate.php', {
                student_id: reg.user_id,
                event_id: reg.event_id,
                certificate_type: type
            });
            showNotification(`${type} certificate generated successfully!`, 'success');
            fetchRegistrations();
        } catch (error) {
            const msg = error.response?.data?.message || 'Failed to generate certificate';
            showNotification(msg, 'error');
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-4 py-8">
            <div className="flex justify-between items-center mb-6">
                <div>
                    <h1 className="text-2xl font-black text-gray-800 tracking-tight">Student List</h1>
                </div>
                <Link
                    to="/admin/students"
                    className="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/30 transition-all active:scale-95"
                >
                    <span>👥 View All Students</span>
                </Link>
            </div>
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {registrations.map((reg) => (
                            <tr key={reg.reg_id}>
                                <td className="px-6 py-4 whitespace-nowrap border-b border-gray-100">
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
                                        <Link to={`/admin/student/${reg.user_id}`} className="text-sm font-bold text-gray-800 hover:text-blue-600 transition-colors">
                                            {reg.student_name}
                                        </Link>
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{reg.event_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${reg.status === 'approved' ? 'bg-green-100 text-green-800' :
                                        reg.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
                                        }`}>
                                        {reg.status}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    {reg.status === 'pending' && (
                                        <>
                                            <button onClick={() => handleStatusUpdate(reg.reg_id, 'approved')} className="text-green-600 hover:text-green-900 border border-green-200 px-2 py-1 rounded hover:bg-green-50">Approve</button>
                                            <button onClick={() => handleStatusUpdate(reg.reg_id, 'rejected')} className="text-red-600 hover:text-red-900 border border-red-200 px-2 py-1 rounded hover:bg-red-50">Reject</button>
                                        </>
                                    )}
                                    {reg.status === 'approved' && (
                                        <div className="flex items-center space-x-3">
                                            {parseInt(reg.is_generated) === 0 ? (
                                                <select
                                                    value={selectedTypes[reg.reg_id] || 'Participation'}
                                                    onChange={(e) => handleTypeChange(reg.reg_id, e.target.value)}
                                                    className="text-xs border border-gray-300 rounded px-2 py-1 outline-none focus:ring-1 focus:ring-blue-500"
                                                >
                                                    {certificateTypes.map(type => (
                                                        <option key={type} value={type}>{type}</option>
                                                    ))}
                                                </select>
                                            ) : (
                                                <span className={`px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${reg.certificate_type === 'First Prize' ? 'bg-yellow-100 text-yellow-700' :
                                                    reg.certificate_type === 'Second Prize' ? 'bg-gray-100 text-gray-700' :
                                                        reg.certificate_type === 'Third Prize' ? 'bg-orange-100 text-orange-700' :
                                                            'bg-blue-100 text-blue-700'
                                                    }`}>
                                                    {reg.certificate_type}
                                                </span>
                                            )}
                                            {parseInt(reg.is_generated) > 0 ? (
                                                <a
                                                    href={`${BASE_URL}/student/download_certificate.php?id=${reg.certificate_id}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="px-3 py-1 rounded text-xs font-bold bg-green-600 hover:bg-green-700 text-white shadow-sm active:scale-95 transition-all inline-flex items-center gap-1"
                                                >
                                                    <span>Open Cert</span>
                                                    <span>📄</span>
                                                </a>
                                            ) : (
                                                <button
                                                    onClick={() => handleGenerateCertificate(reg)}
                                                    className="px-3 py-1 rounded text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm active:scale-95 transition-all"
                                                >
                                                    Generate Cert
                                                </button>
                                            )}
                                        </div>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {registrations.length === 0 && (
                            <tr><td colSpan="4" className="px-6 py-4 text-center text-gray-500">No registrations found</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Registrations;
