import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const StudentDetails = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [student, setStudent] = useState(null);
    const [loading, setLoading] = useState(true);
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchStudentDetails();
    }, [id]);

    const fetchStudentDetails = async () => {
        try {
            const response = await api.get(`/admin/students.php?id=${id}`);
            setStudent(response.data);
        } catch (error) {
            console.error('Error fetching student details:', error);
            showNotification('Failed to load student details', 'error');
            navigate('/admin/students');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (!student) return null;

    const participationRate = student.stats.total_participations > 0
        ? Math.round((student.stats.approved_registrations / student.stats.total_participations) * 100)
        : 0;

    return (
        <div className="max-w-6xl mx-auto space-y-8 pb-12">
            {/* Header / Navigation */}
            <div className="flex items-center justify-between">
                <button
                    onClick={() => navigate('/admin/students')}
                    className="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-blue-600 transition-colors"
                >
                    <span>←</span> Back to Directory
                </button>
                <div className="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    Student ID: #{student.id}
                </div>
            </div>

            {/* Profile Overview Card */}
            <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row gap-8 items-start">
                <div className="w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center text-4xl text-white shadow-lg shrink-0 overflow-hidden">
                    {student.profile_pic ? (
                        <img
                            src={`${BASE_URL.replace('/backend', '')}/${student.profile_pic}?t=${Date.now()}`}
                            alt={student.name}
                            className="w-full h-full object-cover"
                        />
                    ) : (
                        student.name.charAt(0).toUpperCase()
                    )}
                </div>
                <div className="flex-1 space-y-4">
                    <div>
                        <h1 className="text-3xl font-black text-gray-800">{student.name}</h1>
                        <p className="text-gray-500 font-medium">{student.email}</p>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-3 gap-x-8">
                        <InfoItem label="College" value={student.college_name} />
                        <InfoItem label="Course" value={student.course_name} />
                        <InfoItem label="Year" value={student.year_of_study ? `${student.year_of_study} Year` : null} />
                        <InfoItem label="Phone" value={student.phone_number} />
                        <InfoItem label="Joined" value={new Date(student.created_at).toLocaleDateString()} />
                    </div>
                </div>
            </div>

            {/* Performance Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard title="Total Applied" value={student.stats.total_participations} icon="📋" color="blue" />
                <StatCard title="Approved" value={student.stats.approved_registrations} icon="✅" color="green" />
                <StatCard title="Certificates" value={student.stats.total_certificates} icon="🎓" color="yellow" />
                <StatCard title="Approval Rate" value={`${participationRate}%`} icon="📈" color="purple" />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Event History */}
                <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-6">
                    <h2 className="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <span className="p-2 bg-blue-50 rounded-lg">📅</span> Event Participation
                    </h2>
                    <div className="space-y-4">
                        {student.registrations.length > 0 ? (
                            student.registrations.map((reg) => (
                                <div key={reg.reg_id} className="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                                    <div>
                                        <p className="font-bold text-gray-800">{reg.event_name}</p>
                                        <p className="text-[10px] text-gray-400 font-bold uppercase">{new Date(reg.event_date).toLocaleDateString()}</p>
                                    </div>
                                    <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${reg.status === 'approved' ? 'bg-green-100 text-green-700' :
                                        reg.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'
                                        }`}>
                                        {reg.status}
                                    </span>
                                </div>
                            ))
                        ) : (
                            <p className="text-center py-8 text-gray-400 italic text-sm">No events registered yet.</p>
                        )}
                    </div>
                </div>

                {/* Certificate History */}
                <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-6">
                    <h2 className="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <span className="p-2 bg-yellow-50 rounded-lg">🎖️</span> Certificates Earned
                    </h2>
                    <div className="space-y-4">
                        {student.certificates.length > 0 ? (
                            student.certificates.map((cert) => (
                                <div key={cert.id} className="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all hover:bg-white group">
                                    <div>
                                        <p className="font-bold text-gray-800">{cert.event_name}</p>
                                        <p className="text-[10px] text-gray-400 font-bold uppercase">Issued: {new Date(cert.generated_at).toLocaleDateString()}</p>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter">
                                            {cert.certificate_type}
                                        </span>
                                        <a
                                            href={`${BASE_URL}/student/download_certificate.php?id=${cert.id}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="w-8 h-8 flex items-center justify-center bg-gray-100 text-gray-400 group-hover:bg-blue-600 group-hover:text-white rounded-xl transition-all shadow-sm"
                                            title="Open Certificate"
                                        >
                                            📄
                                        </a>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <p className="text-center py-8 text-gray-400 italic text-sm">No certificates earned yet.</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

const InfoItem = ({ label, value }) => (
    <div>
        <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">{label}</p>
        <p className="text-sm font-bold text-gray-700">{value || 'Not provided'}</p>
    </div>
);

const StatCard = ({ title, value, icon, color }) => {
    const colors = {
        blue: 'bg-blue-50 text-blue-600 border-blue-100',
        green: 'bg-green-50 text-green-600 border-green-100',
        yellow: 'bg-yellow-50 text-yellow-600 border-yellow-100',
        purple: 'bg-purple-50 text-purple-600 border-purple-100',
    };
    return (
        <div className={`p-6 rounded-3xl border shadow-sm ${colors[color]}`}>
            <div className="text-3xl mb-4">{icon}</div>
            <p className="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">{title}</p>
            <p className="text-3xl font-black">{value}</p>
        </div>
    );
};

export default StudentDetails;
