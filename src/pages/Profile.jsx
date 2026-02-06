import React, { useState, useEffect } from 'react';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const Profile = () => {
    const [stats, setStats] = useState(null);
    const [profile, setProfile] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isEditing, setIsEditing] = useState(false);
    const [editForm, setEditForm] = useState({});
    const [uploading, setUploading] = useState(false);
    const [previewUrl, setPreviewUrl] = useState(null);
    const [selectedFile, setSelectedFile] = useState(null);

    // Store user id from local storage, but fetch fresh data
    const localUser = JSON.parse(localStorage.getItem('user'));
    const { showNotification } = useNotification();

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const [statsRes, profileRes] = await Promise.all([
                api.get(`/student/profile_stats.php?user_id=${localUser.id}`),
                api.get(`/student/get_profile.php?user_id=${localUser.id}`)
            ]);
            setStats(statsRes.data);
            setProfile(profileRes.data);
            setEditForm(profileRes.data); // Initialize edit form
        } catch (error) {
            console.error('Error fetching data:', error);
            showNotification('Failed to load profile data', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleEditChange = (e) => {
        setEditForm({ ...editForm, [e.target.name]: e.target.value });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setSelectedFile(file);
            setPreviewUrl(URL.createObjectURL(file));
        }
    };

    const handleSaveProfile = async () => {
        try {
            setUploading(true);
            const formData = new FormData();
            formData.append('user_id', profile.id);

            // Append all form fields (excluding profile_pic if a new file is selected)
            Object.keys(editForm).forEach(key => {
                if (editForm[key] !== null && editForm[key] !== undefined) {
                    if (key === 'profile_pic' && selectedFile) return;
                    formData.append(key, editForm[key]);
                }
            });

            // Append file if selected
            if (selectedFile) {
                formData.append('profile_pic', selectedFile);
            }

            const response = await api.post('/student/update_profile.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            console.log('Update Response:', response.data);
            const updatedUser = response.data.user;
            console.log('Updated User from Response:', updatedUser);

            // Deep update of profile state with fresh data from server
            setProfile(updatedUser);
            setEditForm(updatedUser);
            setIsEditing(false);
            setSelectedFile(null);
            setPreviewUrl(null);

            // Comprehensive Local Storage Update
            const currentLocalUser = JSON.parse(localStorage.getItem('user'));
            const newLocalUser = {
                ...currentLocalUser,
                ...updatedUser
            };
            console.log('Setting new local user:', newLocalUser);
            localStorage.setItem('user', JSON.stringify(newLocalUser));

            showNotification('Profile updated successfully', 'success');

            // Reload to sync Sidebar, Navbar and other components
            setTimeout(() => {
                window.location.reload();
            }, 500);

        } catch (error) {
            console.error('Error updating profile:', error);
            const errorMessage = error.response?.data?.message || 'Failed to update profile';
            showNotification(errorMessage, 'error');
        } finally {
            setUploading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (!profile || !stats) {
        return <div className="text-center p-8">Failed to load profile data.</div>;
    }

    const prizeTotal = stats.first_prizes + stats.second_prizes + stats.third_prizes;
    const participationRate = stats.total_registrations > 0
        ? Math.round((stats.total_participations / stats.total_registrations) * 100)
        : 0;

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-700 pb-12">
            {/* 👤 Profile Header */}
            <div className="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <div className="relative z-10 flex flex-col md:flex-row items-center gap-8 justify-between">
                    {/* Left: Avatar & Basic Info */}
                    <div className="flex flex-col md:flex-row items-center gap-8">
                        <div
                            className="relative group cursor-pointer"
                            onClick={() => !isEditing && setIsEditing(true)}
                            title={isEditing ? "Click image to upload" : "Click to edit profile"}
                        >
                            <div className="w-32 h-32 bg-white/20 backdrop-blur-xl rounded-[2rem] flex items-center justify-center text-5xl shadow-2xl border border-white/20 overflow-hidden transition-all group-hover:bg-white/30 group-hover:scale-[1.02]">
                                {previewUrl ? (
                                    <img src={previewUrl} alt="Preview" className="w-full h-full object-cover" />
                                ) : profile.profile_pic ? (
                                    <img src={`${BASE_URL.replace('/backend', '')}/${profile.profile_pic}?t=${Date.now()}`} alt="Profile" className="w-full h-full object-cover" />
                                ) : (
                                    <span className="font-black drop-shadow-md">{profile.name.charAt(0).toUpperCase()}</span>
                                )}

                                {isEditing && (
                                    <label className="absolute inset-0 bg-black/60 flex flex-col items-center justify-center cursor-pointer transition-opacity rounded-2xl">
                                        <span className="text-white text-[10px] font-black uppercase tracking-widest text-center px-4">Click to Change Photo</span>
                                        <input type="file" className="hidden" accept="image/*" onChange={handleFileChange} />
                                    </label>
                                )}
                            </div>

                            {/* ✨ Corner Edit Badge */}
                            <div className={`absolute -bottom-2 -right-2 w-10 h-10 bg-white rounded-2xl shadow-xl flex items-center justify-center transition-all duration-300 ${isEditing ? 'bg-green-500 scale-110 rotate-12' : 'group-hover:scale-110 group-hover:rotate-12'}`}>
                                {isEditing ? (
                                    <span className="text-white text-xl">📸</span>
                                ) : (
                                    <span className="text-blue-600 text-xl">✏️</span>
                                )}
                            </div>
                        </div>

                        <div className="text-center md:text-left flex-grow">
                            <h1 className="text-4xl font-black tracking-tight flex items-center gap-3">
                                {profile.name}
                                <span className={`w-3 h-3 rounded-full bg-green-400 border-2 border-white/20 ${isEditing ? 'animate-pulse' : ''}`}></span>
                            </h1>
                            <p className="text-blue-100 font-medium opacity-90 mt-1">{profile.email}</p>
                            <div className="mt-4 flex flex-wrap justify-center md:justify-start gap-4">
                                <span className="bg-white/10 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-white/10 flex items-center gap-2">
                                    <span className="text-[10px]">🛡️</span> {profile.role}
                                </span>
                                <span className="bg-green-400/20 text-green-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-green-400/20">
                                    Active Student
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Right: Actions or Quick Stats */}
                    <div className="flex flex-col items-end gap-4 min-w-[200px]">
                        {isEditing ? (
                            <div className="flex flex-col gap-3 w-full animate-in slide-in-from-right duration-300">
                                <button
                                    onClick={handleSaveProfile}
                                    disabled={uploading}
                                    className="w-full bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-xl shadow-green-500/20 transition-all active:scale-95 flex items-center justify-center gap-2"
                                >
                                    {uploading ? (
                                        <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                    ) : '✅ Save All Changes'}
                                </button>
                                <button
                                    onClick={() => {
                                        setIsEditing(false);
                                        setPreviewUrl(null);
                                        setSelectedFile(null);
                                        setEditForm(profile);
                                    }}
                                    className="w-full bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-2xl font-bold text-sm border border-white/10 transition-all active:scale-95 text-center"
                                >
                                    Cancel
                                </button>
                            </div>
                        ) : (
                            <div className="flex gap-8 p-6 bg-white/10 backdrop-blur-md rounded-3xl border border-white/10">
                                <div className="text-center group transition-transform hover:scale-110">
                                    <p className="text-3xl font-black mb-1">{stats.total_participations}</p>
                                    <p className="text-[10px] font-bold uppercase tracking-widest opacity-60">Events</p>
                                </div>
                                <div className="w-[1px] h-12 bg-white/20 self-center"></div>
                                <div className="text-center group transition-transform hover:scale-110">
                                    <p className="text-3xl font-black mb-1">{prizeTotal + stats.participation_certificates}</p>
                                    <p className="text-[10px] font-bold uppercase tracking-widest opacity-60">Certificates</p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Decorative background effects */}
                <div className="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div className="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
            </div>

            {/* 📝 Profile Details Section - Only show when editing */}
            {
                isEditing && (
                    <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <div className="flex justify-between items-center mb-6">
                            <h2 className="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <span className="p-2 bg-blue-50 rounded-lg">📋</span> Student Details
                            </h2>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <DetailItem
                                label="Full Name"
                                name="name"
                                value={isEditing ? (editForm.name || '') : profile.name}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                            />
                            <DetailItem
                                label="Email"
                                name="email"
                                value={isEditing ? (editForm.email || '') : profile.email}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                placeholder="Enter email address"
                            />
                            <DetailItem
                                label="College"
                                name="college_name"
                                value={isEditing ? (editForm.college_name || '') : (profile.college_name || 'Not provided')}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                placeholder="Enter college name"
                            />
                            <DetailItem
                                label="Course"
                                name="course_name"
                                value={isEditing ? (editForm.course_name || '') : (profile.course_name || 'Not provided')}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                placeholder="e.g. B.Tech CS"
                            />
                            <DetailItem
                                label="Year of Study"
                                name="year_of_study"
                                value={isEditing ? (editForm.year_of_study || '') : (profile.year_of_study ? `${profile.year_of_study} Year` : 'Not provided')}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                type="select"
                                options={[1, 2, 3, 4, 5]}
                            />
                            <DetailItem
                                label="Phone Number"
                                name="phone_number"
                                value={isEditing ? (editForm.phone_number || '') : (profile.phone_number || 'Not provided')}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                placeholder="Enter phone number"
                            />
                            <DetailItem
                                label="Address"
                                name="address"
                                value={isEditing ? (editForm.address || '') : (profile.address || 'Not provided')}
                                isEditing={isEditing}
                                onChange={handleEditChange}
                                fullWidth
                                placeholder="Enter your address"
                            />
                        </div>
                    </div>
                )
            }

            {/* 📊 Statistics Grid (Existing) */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard title="Events Participated" value={stats.total_participations} icon="🏆" color="blue" />
                <StatCard title="First Prizes" value={stats.first_prizes} icon="🥇" color="yellow" />
                <StatCard title="Second Prizes" value={stats.second_prizes} icon="🥈" color="gray" />
                <StatCard title="Third Prizes" value={stats.third_prizes} icon="🥉" color="orange" />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* 🎯 Performance & Advanced Features */}
                <div className="lg:col-span-2 space-y-8">
                    <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                            <span className="p-2 bg-purple-50 rounded-lg">📊</span> Performance Analytics
                        </h2>

                        <div className="space-y-6">
                            <div>
                                <div className="flex justify-between mb-2">
                                    <span className="text-sm font-bold text-gray-600">Attendance Rate</span>
                                    <span className="text-sm font-black text-blue-600">{participationRate}%</span>
                                </div>
                                <div className="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        className="h-full bg-blue-600 rounded-full transition-all duration-1000"
                                        style={{ width: `${participationRate}%` }}
                                    ></div>
                                </div>
                                <p className="text-[10px] text-gray-400 mt-2 font-medium italic">Ratio of approved registrations vs total applied.</p>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Win Ratio</p>
                                    <p className="text-2xl font-black text-gray-800">
                                        {stats.total_participations > 0
                                            ? Math.round((prizeTotal / stats.total_participations) * 100)
                                            : 0}%
                                    </p>
                                </div>
                                <div className="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                    <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Certificates</p>
                                    <p className="text-2xl font-black text-gray-800">{prizeTotal + stats.participation_certificates}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* 🎖️ Achievement Badges */}
                    <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                        <h2 className="text-xl font-bold text-gray-800 mb-6">Achievement Badges</h2>
                        <div className="flex flex-wrap gap-6">
                            {stats.total_participations >= 1 && <Badge icon="🌱" label="Early Bird" desc="1st Participation" />}
                            {stats.total_participations >= 5 && <Badge icon="🔥" label="Enthusiast" desc="5+ Events" color="orange" />}
                            {stats.first_prizes >= 1 && <Badge icon="⭐" label="Champion" desc="1st Prize Winner" color="yellow" />}
                            {prizeTotal >= 3 && <Badge icon="👑" label="Performer" desc="3+ Prizes" color="indigo" />}
                            {stats.total_participations === 0 && (
                                <p className="text-gray-400 italic text-sm">Participate in events to earn badges!</p>
                            )}
                        </div>
                    </div>
                </div>

                {/* 🕒 Recent Activity */}
                <div className="space-y-6">
                    <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 h-full">
                        <h2 className="text-xl font-bold text-gray-800 mb-6">Recent Activity</h2>
                        <div className="space-y-6">
                            {stats.recent_activity.length > 0 ? (
                                stats.recent_activity.map((act, idx) => (
                                    <div key={idx} className="flex gap-4 relative">
                                        {idx !== stats.recent_activity.length - 1 && (
                                            <div className="absolute left-[11px] top-6 bottom-[-24px] w-[2px] bg-gray-100"></div>
                                        )}
                                        <div className={`w-6 h-6 rounded-full flex-shrink-0 z-10 ${act.status === 'approved' ? 'bg-green-100 border-2 border-green-500' :
                                            act.status === 'rejected' ? 'bg-red-100 border-2 border-red-500' : 'bg-blue-100 border-2 border-blue-500'
                                            }`}></div>
                                        <div>
                                            <p className="text-sm font-bold text-gray-800 leading-tight">{act.event_name}</p>
                                            <p className="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                                {act.status} • {new Date(act.event_date).toLocaleDateString()}
                                            </p>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-gray-400 italic text-sm text-center py-12">No recent activity.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div >
    );
};

// Helper Item Component for Details
const DetailItem = ({ label, name, value, isEditing, onChange, fullWidth, placeholder, type = 'text', options }) => (
    <div className={`flex flex-col ${fullWidth ? 'col-span-1 md:col-span-2 lg:col-span-3' : ''}`}>
        <label className="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">{label}</label>
        {isEditing ? (
            type === 'select' ? (
                <select
                    name={name}
                    value={value ? String(value).replace(' Year', '') : ''} // Safely handle value
                    onChange={onChange}
                    className="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                >
                    <option value="">Select Year</option>
                    {options.map(opt => <option key={opt} value={opt}>{opt} Year</option>)}
                </select>
            ) : (
                <input
                    type="text"
                    name={name}
                    value={value === 'Not provided' ? '' : value}
                    onChange={onChange}
                    placeholder={placeholder}
                    className="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all w-full"
                />
            )
        ) : (
            <div className="bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                {value}
            </div>
        )}
    </div>
);

const StatCard = ({ title, value, icon, color }) => {
    const colors = {
        blue: 'bg-blue-50 text-blue-600 border-blue-100',
        yellow: 'bg-yellow-50 text-yellow-600 border-yellow-100',
        gray: 'bg-gray-50 text-gray-600 border-gray-100',
        orange: 'bg-orange-50 text-orange-600 border-orange-100',
        indigo: 'bg-indigo-50 text-indigo-600 border-indigo-100',
    };

    return (
        <div className={`p-6 rounded-3xl border shadow-sm transition-all hover:shadow-md ${colors[color]}`}>
            <div className="text-3xl mb-4">{icon}</div>
            <p className="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">{title}</p>
            <p className="text-3xl font-black">{value}</p>
        </div>
    );
};

const Badge = ({ icon, label, desc, color = 'blue' }) => {
    const bgColors = {
        blue: 'bg-blue-50 border-blue-100',
        yellow: 'bg-yellow-50 border-yellow-100',
        orange: 'bg-orange-50 border-orange-100',
        indigo: 'bg-indigo-50 border-indigo-100',
    };
    return (
        <div className={`flex flex-col items-center p-4 rounded-2xl border ${bgColors[color]} w-28 text-center transition-transform hover:scale-105`}>
            <div className="text-3xl mb-2">{icon}</div>
            <p className="text-xs font-black text-gray-800 leading-tight">{label}</p>
            <p className="text-[9px] text-gray-500 font-medium mt-1 uppercase tracking-tighter">{desc}</p>
        </div>
    );
};

export default Profile;
