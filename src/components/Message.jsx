import React, { useEffect } from 'react';

const Message = ({ message, type = 'success', onClose }) => {
    useEffect(() => {
        if (onClose) {
            const timer = setTimeout(() => {
                onClose();
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [onClose]);

    if (!message) return null;

    return (
        <div className="mb-6 animate-fadeIn">
            <div className="bg-green-600 text-white px-6 py-4 rounded-lg shadow-md flex items-center justify-between border-l-8 border-green-800">
                <div className="flex items-center">
                    <svg className="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span className="font-semibold">{message}</span>
                </div>
                {onClose && (
                    <button onClick={onClose} className="hover:opacity-75 transition-opacity">
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                )}
            </div>
        </div>
    );
};

export default Message;
