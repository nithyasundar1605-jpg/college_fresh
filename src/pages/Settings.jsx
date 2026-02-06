import React, { useState } from 'react';
import api from '../services/api';
import { useNotification } from '../context/NotificationContext';
import { useTheme } from '../context/ThemeContext';

const Settings = () => {
    const [activeTab, setActiveTab] = useState('account');
    const { showNotification } = useNotification();
    const { theme, toggleTheme, colorMode, setColorMode } = useTheme();
    const user = JSON.parse(localStorage.getItem('user'));

    // Password State
    const [passwords, setPasswords] = useState({
        current: '',
        new: '',
        confirm: ''
    });

    const handlePasswordChange = (e) => {
        setPasswords({ ...passwords, [e.target.name]: e.target.value });
    };

    const updatePassword = async (e) => {
        e.preventDefault();
        if (passwords.new !== passwords.confirm) {
            showNotification('New passwords do not match', 'error');
            return;
        }
        if (passwords.new.length < 6) {
            showNotification('Password must be at least 6 characters', 'error');
            return;
        }

        try {
            await api.post('/auth/change_password.php', {
                user_id: user.id,
                current_password: passwords.current,
                new_password: passwords.new
            });
            showNotification('Password updated successfully', 'success');
            setPasswords({ current: '', new: '', confirm: '' });
        } catch (error) {
            const msg = error.response?.data?.message || 'Failed to update password';
            showNotification(msg, 'error');
        }
    };

    return (
        <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
            <h1 className="text-3xl font-black text-[var(--text-main)]">Settings</h1>

            {/* Tabs */}
            <div className="flex border-b border-[var(--border-color)] space-x-8">
                {['account', 'appearance', 'notifications'].map((tab) => (
                    <button
                        key={tab}
                        onClick={() => setActiveTab(tab)}
                        className={`pb-4 text-sm font-bold uppercase tracking-wider transition-all ${activeTab === tab
                            ? 'border-b-2 border-blue-600 text-blue-600'
                            : 'text-[var(--text-light)] hover:text-[var(--text-main)]'
                            }`}
                    >
                        {tab.replace('-', ' ')}
                    </button>
                ))}
            </div>

            {/* Content Area */}
            <div className="bg-[var(--card-bg)] rounded-3xl p-8 shadow-sm border border-[var(--border-color)]">

                {/* 🔒 Account & Security */}
                {activeTab === 'account' && (
                    <div className="space-y-8">
                        <div>
                            <h2 className="text-xl font-bold text-[var(--text-main)] mb-2">Change Password</h2>
                            <p className="text-sm text-[var(--text-light)]">Ensure your account is using a long, random password to stay secure.</p>
                        </div>
                        <form onSubmit={updatePassword} className="space-y-4 max-w-md">
                            <div>
                                <label className="block text-xs font-bold text-[var(--text-light)] uppercase mb-1">Current Password</label>
                                <input
                                    type="password"
                                    name="current"
                                    value={passwords.current}
                                    onChange={handlePasswordChange}
                                    className="w-full bg-[var(--bg-main)] border border-[var(--border-color)] text-[var(--text-main)] rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-[var(--text-light)] uppercase mb-1">New Password</label>
                                <input
                                    type="password"
                                    name="new"
                                    value={passwords.new}
                                    onChange={handlePasswordChange}
                                    className="w-full bg-[var(--bg-main)] border border-[var(--border-color)] text-[var(--text-main)] rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-[var(--text-light)] uppercase mb-1">Confirm New Password</label>
                                <input
                                    type="password"
                                    name="confirm"
                                    value={passwords.confirm}
                                    onChange={handlePasswordChange}
                                    className="w-full bg-[var(--bg-main)] border border-[var(--border-color)] text-[var(--text-main)] rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    required
                                />
                            </div>
                            <button
                                type="submit"
                                className="bg-blue-600 text-white font-bold py-2 px-6 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30"
                            >
                                Update Password
                            </button>
                        </form>
                    </div>
                )}

                {/* 🎨 Appearance */}
                {activeTab === 'appearance' && (
                    <div className="space-y-8">
                        <div>
                            <h2 className="text-xl font-bold text-[var(--text-main)] mb-2">Theme Customization</h2>
                            <p className="text-sm text-[var(--text-light)]">Manage your interface look and feel.</p>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="p-6 rounded-2xl border border-[var(--border-color)] bg-[var(--bg-main)]">
                                <h3 className="font-bold text-[var(--text-main)] mb-4">Mode</h3>
                                <div className="flex gap-4">
                                    <button
                                        onClick={() => theme === 'dark' && toggleTheme()}
                                        className={`flex-1 py-3 rounded-xl border-2 font-bold transition-all ${theme === 'light'
                                            ? 'border-blue-600 bg-blue-50 text-blue-600'
                                            : 'border-transparent bg-white text-gray-500'}`}
                                    >
                                        ☀️ Light
                                    </button>
                                    <button
                                        onClick={() => theme === 'light' && toggleTheme()}
                                        className={`flex-1 py-3 rounded-xl border-2 font-bold transition-all ${theme === 'dark'
                                            ? 'border-blue-500 bg-gray-800 text-blue-400'
                                            : 'border-transparent bg-gray-200 text-gray-500'}`}
                                    >
                                        🌙 Dark
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* 🔔 Notifications & Features (Placeholder) */}
                {activeTab === 'notifications' && (
                    <div className="space-y-8">
                        <div>
                            <h2 className="text-xl font-bold text-[var(--text-main)] mb-2">New Features & Notifications</h2>
                            <p className="text-sm text-[var(--text-light)]">Control your notification preferences and experimental features.</p>
                        </div>

                        <div className="space-y-4">
                            <ToggleItem label="Email Notifications" desc="Receive emails about event updates" defaultChecked />
                            <ToggleItem label="Browser Push Notifications" desc="Get notified instantly on your device" />
                            <ToggleItem label="Beta Features Access" desc="Try out new features before they are released" />
                            <ToggleItem label="Compact View" desc="Reduce padding and font size for denser information" />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

// Helper Toggle Component
const ToggleItem = ({ label, desc, defaultChecked = false }) => {
    const [checked, setChecked] = useState(defaultChecked);
    return (
        <div className="flex items-center justify-between p-4 rounded-xl border border-[var(--border-color)] hover:bg-[var(--bg-main)] transition-colors">
            <div>
                <p className="font-bold text-[var(--text-main)]">{label}</p>
                <p className="text-xs text-[var(--text-light)]">{desc}</p>
            </div>
            <button
                onClick={() => setChecked(!checked)}
                className={`w-12 h-6 rounded-full p-1 transition-colors ${checked ? 'bg-blue-600' : 'bg-gray-300'}`}
            >
                <div className={`w-4 h-4 bg-white rounded-full transition-transform ${checked ? 'translate-x-6' : 'translate-x-0'}`} />
            </button>
        </div>
    );
};

export default Settings;
