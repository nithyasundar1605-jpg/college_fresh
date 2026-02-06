import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

const EventCalendar = () => {
    const [events, setEvents] = useState([]);
    const [currentDate, setCurrentDate] = useState(new Date());
    const [selectedDate, setSelectedDate] = useState(new Date());
    const [loading, setLoading] = useState(true);
    const user = JSON.parse(localStorage.getItem('user'));
    const navigate = useNavigate();

    useEffect(() => {
        fetchEvents();
    }, []);

    const fetchEvents = async () => {
        try {
            const response = await api.get(`/student/events.php?user_id=${user.id}`);
            setEvents(response.data);
        } catch (error) {
            console.error('Error fetching events', error);
        } finally {
            setLoading(false);
        }
    };

    // Calendar Logic
    const getDaysInMonth = (date) => {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    };

    const getFirstDayOfMonth = (date) => {
        return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    };

    const daysInMonth = getDaysInMonth(currentDate);
    const firstDay = getFirstDayOfMonth(currentDate);

    // Generate calendar grid
    const calendarDays = [];
    // Empty slots
    for (let i = 0; i < firstDay; i++) {
        calendarDays.push(null);
    }
    // Days
    for (let i = 1; i <= daysInMonth; i++) {
        calendarDays.push(new Date(currentDate.getFullYear(), currentDate.getMonth(), i));
    }

    const nextMonth = () => {
        setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1));
    };

    const prevMonth = () => {
        setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1));
    };

    const isSameDay = (d1, d2) => {
        return d1.getDate() === d2.getDate() &&
            d1.getMonth() === d2.getMonth() &&
            d1.getFullYear() === d2.getFullYear();
    };

    const getEventsForDay = (date) => {
        if (!date) return [];
        return events.filter(e => {
            // Fix date parsing if string "YYYY-MM-DD"
            const [y, m, d] = e.event_date.split('-');
            const eDate = new Date(y, m - 1, d);
            return isSameDay(eDate, date);
        });
    };

    const selectedEvents = getEventsForDay(selectedDate);

    if (loading) return <div className="p-10 text-center">Loading Calendar...</div>;

    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    return (
        <div className="max-w-6xl mx-auto space-y-8 animate-in fade-in duration-500">
            <div className="text-center mb-8">
                <h1 className="text-4xl font-extrabold text-gray-800 mb-2">📅 Event Calendar</h1>
                <p className="text-xl text-gray-500">Plan your schedule with our interactive event guide.</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Calendar Side */}
                <div className="lg:col-span-2 bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden flex flex-col">
                    {/* Header */}
                    <div className="bg-blue-600 p-6 text-white flex justify-between items-center">
                        <button onClick={prevMonth} className="hover:bg-blue-700 p-2 rounded-full transition-colors">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <h2 className="text-2xl font-bold">
                            {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
                        </h2>
                        <button onClick={nextMonth} className="hover:bg-blue-700 p-2 rounded-full transition-colors">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    {/* Grid Header */}
                    <div className="grid grid-cols-7 text-center bg-gray-50 border-b border-gray-200">
                        {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
                            <div key={day} className="py-3 text-xs font-bold text-gray-400 uppercase tracking-widest">{day}</div>
                        ))}
                    </div>

                    {/* Grid Body */}
                    <div className="grid grid-cols-7 flex-grow min-h-[400px]">
                        {calendarDays.map((date, idx) => {
                            if (!date) return <div key={idx} className="bg-gray-50/30 border-b border-r border-gray-100"></div>;

                            const dayEvents = getEventsForDay(date);
                            const isSelected = isSameDay(date, selectedDate);
                            const isToday = isSameDay(date, new Date());

                            return (
                                <div
                                    key={idx}
                                    onClick={() => setSelectedDate(date)}
                                    className={`relative border-b border-r border-gray-100 p-2 cursor-pointer transition-all hover:bg-blue-50 flex flex-col items-center justify-start h-24 sm:h-auto
                                        ${isSelected ? 'bg-blue-50 ring-2 ring-inset ring-blue-500' : ''}
                                        ${isToday ? 'bg-yellow-50' : ''}
                                    `}
                                >
                                    <span className={`text-sm font-semibold w-7 h-7 flex items-center justify-center rounded-full mb-1
                                        ${isToday ? 'bg-yellow-400 text-white' : 'text-gray-700'}
                                    `}>
                                        {date.getDate()}
                                    </span>

                                    {/* Event Dots */}
                                    <div className="flex flex-wrap gap-1 justify-center max-w-full">
                                        {dayEvents.map((ev, i) => (
                                            <div key={i} className={`w-2 h-2 rounded-full ${ev.status === 'open' ? 'bg-green-500' : 'bg-red-400'}`} title={ev.event_name}></div>
                                        ))}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Details Side */}
                <div className="bg-white rounded-3xl shadow-md border border-gray-100 p-6 h-full min-h-[400px]">
                    <h3 className="text-xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">
                        Events for {selectedDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric' })}
                    </h3>

                    {selectedEvents.length === 0 ? (
                        <div className="text-center py-10 text-gray-400">
                            <div className="text-4xl mb-2">📅</div>
                            <p>No events scheduled for this day.</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {selectedEvents.map(event => (
                                <div
                                    key={event.event_id}
                                    onClick={() => navigate(`/student/event/${event.event_id}`)}
                                    className="block p-4 rounded-xl border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer group bg-gray-50 hover:bg-white"
                                >
                                    <div className="flex justify-between items-start mb-2">
                                        <h4 className="font-bold text-gray-800 group-hover:text-blue-600 transition-colors line-clamp-1">
                                            {event.event_name}
                                        </h4>
                                        <span className={`text-[10px] px-2 py-1 rounded-full uppercase font-bold ${event.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                            {event.status}
                                        </span>
                                    </div>
                                    <p className="text-xs text-gray-500 mb-2 flex items-center">
                                        📍 {event.venue}
                                    </p>
                                    <div className="text-xs text-blue-500 font-bold group-hover:underline">
                                        View Details →
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default EventCalendar;
