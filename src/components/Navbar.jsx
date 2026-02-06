import React from 'react';
import { useNavigate } from 'react-router-dom';
import NotificationDropdown from './NotificationDropdown';
import { BASE_URL } from '../services/api';

const Navbar = () => {
    const user = JSON.parse(localStorage.getItem('user'));
    const navigate = useNavigate();
    const cleanBaseUrl = BASE_URL.replace('/backend', '');

    const handleLogout = () => {
        localStorage.removeItem('user');
        navigate('/login');
    };

    return (
        <nav className="bg-[var(--nav-bg)] border-b border-[var(--nav-border)] h-16 flex items-center justify-between px-6 z-50 relative shadow-lg">
            <div className="md:hidden text-xl font-bold text-white tracking-tight">
                College Event
            </div>
            <div className="hidden md:block">
                <h2 className="text-white font-bold text-lg tracking-wide uppercase">College Event Management</h2>
            </div>
            <div className="flex items-center space-x-6">
                {user ? (
                    <>
                        <div className="flex items-center space-x-3">
                            {user.profile_pic ? (
                                <img
                                    src={`${cleanBaseUrl}/${user.profile_pic}?t=${Date.now()}`}
                                    alt="Profile"
                                    className="w-9 h-9 rounded-xl object-cover border-2 border-white/20"
                                />
                            ) : (
                                <span className="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold text-xs ring-2 ring-white/10">
                                    {user.name.split(' ').map(n => n[0]).join('')}
                                </span>
                            )}
                            <div className="hidden sm:flex flex-col">
                                <span className="text-white font-bold text-sm leading-none">Welcome, {user.name}</span>
                                <span className="text-blue-200 text-[10px] font-black uppercase tracking-widest mt-0.5">{user.role}</span>
                            </div>
                        </div>

                        <NotificationDropdown />

                        <button
                            onClick={handleLogout}
                            className="px-4 py-2 text-sm font-bold text-white bg-[var(--soft-red)] rounded-lg hover:bg-[var(--soft-red-hover)] transition-all shadow-sm active:scale-95"
                        >
                            Logout
                        </button>
                    </>
                ) : (
                    <div className="text-white/80 italic text-sm">Please Login</div>
                )}
            </div>
        </nav>
    );
};

export default Navbar;
