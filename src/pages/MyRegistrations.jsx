import React, { useEffect, useState } from 'react';
import api, { BASE_URL } from '../services/api';

const MyRegistrations = () => {
    const [registrations, setRegistrations] = useState([]);
    const user = JSON.parse(localStorage.getItem('user'));

    useEffect(() => {
        fetchRegistrations();
    }, []);

    const fetchRegistrations = async () => {
        try {
            const response = await api.get(`/student/my_registrations.php?user_id=${user.id}`);
            setRegistrations(response.data);
        } catch (error) {
            console.error('Error fetching registrations:', error);
        }
    };

    const downloadCertificate = (certificateId) => {
        // Direct link to download using centralized BASE_URL
        window.open(`${BASE_URL}/student/download_certificate.php?id=${certificateId}`, '_blank');
    };

    return (
        <div>
            <h1 className="text-2xl font-bold text-gray-800 mb-6">My Registrations</h1>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {registrations.map((reg) => (
                            <tr key={reg.reg_id}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{reg.event_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{reg.event_date}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{reg.venue}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${reg.status === 'approved' ? 'bg-green-100 text-green-800' :
                                        reg.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            'bg-yellow-100 text-yellow-800'
                                        }`}>
                                        {reg.status}
                                    </span>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    {reg.status === 'approved' && reg.certificate_id ? (
                                        <button
                                            onClick={() => downloadCertificate(reg.certificate_id)}
                                            className="text-blue-600 hover:text-blue-900 font-bold transition-colors"
                                        >
                                            Download PDF
                                        </button>
                                    ) : (
                                        <span className="text-gray-400">Not Available</span>
                                    )}
                                </td>
                            </tr>
                        ))}
                        {registrations.length === 0 && (
                            <tr><td colSpan="5" className="px-6 py-4 text-center text-gray-500">You haven't registered for any events yet.</td></tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default MyRegistrations;
