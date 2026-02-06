import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const AdminDashboard = () => {
    const navigate = useNavigate();
    const [stats, setStats] = useState({
        total_events: 0,
        total_students: 0,
        total_registrations: 0,
        certificates_issued: 0,
        recent_events: []
    });

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            const response = await api.get('/admin/dashboard.php');
            setStats(response.data);
        } catch (error) {
            console.error('Error fetching stats:', error);
        }
    };

    const StatCard = ({ title, value, bgColor, link }) => (
        <div
            onClick={() => link && navigate(link)}
            className={`p-6 rounded-2xl shadow-sm transition-all hover:shadow-md ${bgColor} text-white cursor-pointer hover:scale-[1.02] active:scale-[0.98]`}
        >
            <h3 className="text-white/80 text-xs font-bold uppercase tracking-widest mb-1">{title}</h3>
            <p className="text-4xl font-black">{value}</p>
        </div>
    );

    return (
        <div>
            <h1 className="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <StatCard
                    title="Total Events"
                    value={stats.total_events}
                    bgColor="bg-blue-600"
                    link="/admin/events"
                />
                <StatCard
                    title="Total Students"
                    value={stats.total_students}
                    bgColor="bg-emerald-600"
                    link="/admin/registrations"
                />
                <StatCard
                    title="Registrations"
                    value={stats.total_registrations}
                    bgColor="bg-orange-600"
                    link="/admin/registrations"
                />
                <StatCard
                    title="Certificates"
                    value={stats.certificates_issued}
                    bgColor="bg-violet-600"
                    link="/admin/certificate-generation"
                />
            </div>

            <div className="bg-white rounded-lg shadow-md p-6">
                <h2 className="text-xl font-bold text-gray-800 mb-4">Recent Events</h2>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {stats.recent_events.map((event) => (
                                <tr key={event.event_id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{event.event_name}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{event.event_date}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{event.venue}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${event.status === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            }`}>
                                            {event.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                            {stats.recent_events.length === 0 && (
                                <tr><td colSpan="4" className="px-6 py-4 text-center text-gray-500">No events found</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default AdminDashboard;
