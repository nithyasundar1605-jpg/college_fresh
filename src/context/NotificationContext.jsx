import React, { createContext, useState, useContext, useCallback } from 'react';

const NotificationContext = createContext();

export const NotificationProvider = ({ children }) => {
    const [notification, setNotification] = useState(null);
    const [confirmData, setConfirmData] = useState(null);

    const showNotification = useCallback((message, type = 'success') => {
        setNotification({ message, type });
    }, []);

    const closeNotification = useCallback(() => {
        setNotification(null);
    }, []);

    const showConfirm = useCallback((title, message, onConfirm) => {
        setConfirmData({ title, message, onConfirm });
    }, []);

    const handleConfirmAction = () => {
        if (confirmData && confirmData.onConfirm) {
            confirmData.onConfirm();
        }
        setConfirmData(null);
    };

    return (
        <NotificationContext.Provider value={{ showNotification, showConfirm, notification, closeNotification }}>
            {children}

            {/* Confirmation Modal */}
            {confirmData && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 p-4">
                    <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                        <h3 className="text-xl font-bold text-gray-900 mb-2">{confirmData.title}</h3>
                        <p className="text-gray-600 mb-6">{confirmData.message}</p>
                        <div className="flex justify-end space-x-3">
                            <button
                                onClick={() => setConfirmData(null)}
                                className="px-4 py-2 text-gray-500 hover:text-gray-700 font-medium"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handleConfirmAction}
                                className="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-colors"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </NotificationContext.Provider>
    );
};

export const useNotification = () => useContext(NotificationContext);
