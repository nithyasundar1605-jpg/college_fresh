import React, { useEffect } from 'react';

const NotificationToast = ({ message, type = 'success', onClose }) => {
    useEffect(() => {
        const timer = setTimeout(() => {
            onClose();
        }, 3000);
        return () => clearTimeout(timer);
    }, [onClose]);

    const bgColors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-orange-500',
    };

    return (
        <div className={`fixed top-4 right-4 z-50 transform transition-all duration-300 ease-in-out`}>
            <div className={`${bgColors[type] || bgColors.success} text-white px-6 py-4 rounded-lg shadow-lg flex items-center min-w-[300px]`}>
                <div className="mr-3">
                    {type === 'success' && (
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                    )}
                    {type === 'error' && (
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    )}
                </div>
                <div className="font-medium text-lg">{message}</div>
            </div>
        </div>
    );
};

export default NotificationToast;
