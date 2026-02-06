import React, { useEffect, useState } from 'react';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const MyCertificates = () => {
    const [certificates, setCertificates] = useState([]);
    const [loading, setLoading] = useState(true);
    const user = JSON.parse(localStorage.getItem('user'));
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchCertificates();
    }, []);

    const fetchCertificates = async () => {
        try {
            const response = await api.get(`/student/my_certificates.php?student_id=${user.id}`);
            setCertificates(response.data);
        } catch (error) {
            console.error('Error fetching certificates:', error);
            showNotification('Failed to load certificates', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleDownload = (cert) => {
        window.open(`${BASE_URL}/student/download_certificate.php?id=${cert.id}`, '_blank');
    };

    if (loading) {
        return <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>;
    }

    return (
        <div>
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-800">My Certificates</h1>
                <p className="text-gray-600 mt-2">View and download your earned certificates here.</p>
            </div>

            {certificates.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {certificates.map((cert) => (
                        <div key={cert.id} className="bg-white rounded-xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow">
                            <div className="flex items-center mb-4">
                                <div className="bg-green-100 p-3 rounded-full mr-4">
                                    <span className="text-2xl">🎓</span>
                                </div>
                                <div>
                                    <h3 className="text-lg font-bold text-gray-800 leading-tight">{cert.event_name}</h3>
                                    <p className="text-sm text-gray-500">{new Date(cert.generated_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div className="bg-gray-50 rounded-lg p-4 mb-6 border border-dashed border-gray-200">
                                <div className="flex justify-between items-center mb-2">
                                    <p className="text-xs text-uppercase text-gray-400 font-bold tracking-wider">EVENT DATE</p>
                                    <span className="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">
                                        {cert.certificate_type}
                                    </span>
                                </div>
                                <p className="text-gray-700 font-medium">{cert.event_date}</p>
                            </div>
                            <button
                                onClick={() => handleDownload(cert)}
                                className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center transition-colors"
                            >
                                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download as PDF
                            </button>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-2xl mx-auto">
                    <div className="text-6xl mb-4 opacity-20">📜</div>
                    <h3 className="text-xl font-bold text-gray-800 mb-2">Certificate not generated yet</h3>
                    <p className="text-gray-500">
                        Once the administrator generates your certificate for an event you participated in, it will appear here.
                    </p>
                </div>
            )}
        </div>
    );
};

export default MyCertificates;
