import React from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

const Sidebar = () => {
    const location = useLocation();
    const user = JSON.parse(localStorage.getItem('user'));

    // If not logged in, don't show sidebar (or handle in Layout)
    if (!user) return null;

    const adminLinks = [
        { path: '/admin/dashboard', label: 'Dashboard' },
        { path: '/admin/students', label: 'Students' },
        { path: '/admin/registrations', label: 'Student List' },
        { path: '/admin/events', label: 'Events Management' },
        { path: '/admin/certificate-generation', label: 'Certificate Generation' },
        { path: '/admin/reports', label: 'Reports' },
        { path: '/admin/settings', label: 'Settings' },
    ];

    const studentLinks = [
        { path: '/student/dashboard', label: 'Dashboard' },
        { path: '/student/events', label: 'Events' },
        { path: '/student/my-registrations', label: 'My Registrations' },
        { path: '/student/my-certificates', label: 'My Certificates' },
        { path: '/student/gallery', label: 'Event Highlights' },
        { path: '/student/calendar', label: 'Calendar' },
        { path: '/student/profile', label: 'Profile' },
        { path: '/student/settings', label: 'Settings' },
    ];

    const links = user.role === 'admin' ? adminLinks : studentLinks;

    return (
        <div className="w-64 bg-[var(--sidebar-bg)] h-full hidden md:block relative z-20 border-r border-gray-100">
            <nav className="mt-8">
                {links.map((link) => {
                    const isActive = location.pathname === link.path;
                    return (
                        <Link
                            key={link.path}
                            to={link.path}
                            className={`group relative flex items-center px-8 py-4 text-sm font-bold transition-all duration-200 ${isActive
                                ? 'bg-[var(--sidebar-active)] text-white'
                                : 'text-[var(--text-light)] hover:bg-[var(--sidebar-hover)] hover:text-white'
                                }`}
                        >
                            {/* Left Indicator bar */}
                            {isActive && (
                                <span className="absolute left-0 top-0 bottom-0 w-1 bg-[var(--sidebar-indicator)] shadow-[0_0_10px_rgba(37,99,235,0.4)]"></span>
                            )}

                            <span className={`mr-4 transition-colors ${isActive ? 'text-white' : 'text-[var(--icon-muted)] group-hover:text-white'}`}>
                                {/* Simple dot or placeholder icon if none provided */}
                                <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
                            </span>

                            {link.label}
                        </Link>
                    );
                })}
            </nav>
            <div className="absolute bottom-8 left-8">
                <p className="text-[var(--text-light)] text-[10px] uppercase font-bold tracking-widest opacity-40">College Portal v2.0</p>
            </div>
        </div>
    );
};

export default Sidebar;
