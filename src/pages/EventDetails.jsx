import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const EventDetails = () => {
    const { id } = useParams();
    const [event, setEvent] = useState(null);
    const [gallery, setGallery] = useState([]);
    const [loading, setLoading] = useState(false);
    const [highlights, setHighlights] = useState(null);
    const [lightboxIndex, setLightboxIndex] = useState(-1); // -1 means closed
    const user = JSON.parse(localStorage.getItem('user'));
    const navigate = useNavigate();
    const { showNotification, showConfirm } = useNotification();

    useEffect(() => {
        if (id) {
            fetchEvent();
            fetchGallery();
            fetchHighlights();
        }
    }, [id]);

    const fetchEvent = async () => {
        try {
            const response = await api.get(`/student/events.php?user_id=${user.id}`);
            const found = response.data.find(e => e.event_id == id);
            if (found) {
                setEvent(found);
            }
        } catch (error) {
            console.error(error);
            showNotification('Failed to load event details', 'error');
        }
    };

    const fetchGallery = async () => {
        try {
            const response = await api.get(`/gallery_api.php?event_id=${id}&action=list`);
            setGallery(response.data);
        } catch (error) {
            console.error('Failed to load gallery', error);
        }
    };

    const fetchHighlights = async () => {
        try {
            const response = await api.get(`/highlights_info.php?event_id=${id}`);
            if (response.data) setHighlights(response.data);
        } catch (error) {
            console.error('Failed to load highlights info', error);
        }
    };

    const openLightbox = (index) => setLightboxIndex(index);
    const closeLightbox = () => setLightboxIndex(-1);

    const nextImage = (e) => {
        e.stopPropagation();
        setLightboxIndex((prev) => (prev + 1) % gallery.length);
    };

    const prevImage = (e) => {
        e.stopPropagation();
        setLightboxIndex((prev) => (prev - 1 + gallery.length) % gallery.length);
    };

    // Keyboard navigation
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (lightboxIndex === -1) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage(e);
            if (e.key === 'ArrowLeft') prevImage(e);
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [lightboxIndex]);

    const handleRegister = async () => {
        showConfirm(
            'Confirm Registration',
            `Are you sure you want to register for "${event.event_name}"?`,
            async () => {
                setLoading(true);
                try {
                    await api.post('/student/register_event.php', {
                        user_id: user.id,
                        event_id: id
                    });
                    showNotification('Successfully registered for the event', 'success');
                    fetchEvent(); // Refresh to update status
                } catch (error) {
                    const msg = error.response?.data?.message || 'Registration failed';
                    showNotification(msg, 'error');
                } finally {
                    setLoading(false);
                }
            }
        );
    };

    if (!event) return <div className="text-center py-10">Loading event details...</div>;

    const isRegistered = parseInt(event.is_registered) > 0;

    return (
        <div className="bg-white rounded-xl shadow-lg p-8 max-w-4xl mx-auto mt-6 border border-gray-100">
            <button
                onClick={() => navigate(-1)}
                className="mb-6 flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors"
            >
                <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Events
            </button>

            <div className="border-b border-gray-100 pb-8 mb-8">
                <div className="flex justify-between items-start mb-4">
                    <h1 className="text-4xl font-extrabold text-gray-900 leading-tight">{event.event_name}</h1>
                    <span className={`px-4 py-1.5 text-xs font-bold rounded-full uppercase tracking-widest ${event.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                        }`}>
                        {event.status}
                    </span>
                </div>

                {event.image_url && (
                    <div className="mb-8 rounded-2xl overflow-hidden shadow-md max-h-96">
                        <img
                            src={`${BASE_URL}/${event.image_url}`}
                            alt={event.event_name}
                            className="w-full h-full object-cover"
                        />
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-600">
                    <div className="flex items-center">
                        <div className="bg-blue-50 p-2 rounded-lg mr-3">
                            <span className="text-xl">📅</span>
                        </div>
                        <div>
                            <p className="text-xs text-gray-400 font-bold uppercase">Date</p>
                            <p className="font-semibold text-gray-800">{event.event_date}</p>
                        </div>
                    </div>
                    <div className="flex items-center">
                        <div className="bg-blue-50 p-2 rounded-lg mr-3">
                            <span className="text-xl">📍</span>
                        </div>
                        <div>
                            <p className="text-xs text-gray-400 font-bold uppercase">Venue</p>
                            <p className="font-semibold text-gray-800">{event.venue}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div className="prose max-w-none mb-10">
                <h3 className="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-600 pl-4 uppercase text-sm tracking-wider">About this Event</h3>
                <p className="text-gray-600 leading-relaxed text-lg whitespace-pre-line">{event.description}</p>
            </div>

            {gallery.length > 0 && (
                <div className="mb-10">
                    <h3 className="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-pink-500 pl-4 uppercase text-sm tracking-wider">Event Highlights</h3>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {gallery.map((img, index) => (
                            <div key={img.id} className="rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all h-48 cursor-pointer group relative">
                                <div className="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center pointer-events-none">
                                    <span className="text-white font-bold tracking-widest text-sm uppercase">View</span>
                                </div>
                                <img
                                    src={`${BASE_URL}/${img.image_path}`}
                                    alt="Highlight"
                                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    onClick={() => openLightbox(index)}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Lightbox Overlay */}
            {lightboxIndex !== -1 && (
                <div
                    className="fixed inset-0 z-50 bg-black/95 flex items-center justify-center p-4 backdrop-blur-sm animate-in fade-in duration-200"
                    onClick={closeLightbox}
                >
                    <button
                        onClick={closeLightbox}
                        className="absolute top-6 right-6 text-white/50 hover:text-white transition-colors p-2"
                    >
                        <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <button
                        onClick={prevImage}
                        className="absolute left-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-4 rounded-full hover:bg-white/10 transition-all hidden md:block"
                    >
                        <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <img
                        src={`${BASE_URL}/${gallery[lightboxIndex].image_path}`}
                        alt="Full View"
                        className="max-h-[90vh] max-w-[90vw] object-contain shadow-2xl rounded-sm"
                        onClick={(e) => e.stopPropagation()}
                    />

                    <button
                        onClick={nextImage}
                        className="absolute right-4 top-1/2 -translate-y-1/2 text-white/50 hover:text-white p-4 rounded-full hover:bg-white/10 transition-all hidden md:block"
                    >
                        <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <div className="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/50 text-sm font-medium tracking-widest uppercase">
                        {lightboxIndex + 1} / {gallery.length}
                    </div>
                </div>
            )}

            {/* --- ADAVANCED HIGHLIGHTS SECTION --- */}
            {highlights && (
                <div className="space-y-12 animate-in slide-in-from-bottom duration-700 delay-200">

                    {/* Summary */}
                    {highlights.summary && (
                        <div className="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-3xl p-8 shadow-sm border border-indigo-100">
                            <h3 className="text-2xl font-bold text-indigo-900 mb-4 flex items-center gap-2">
                                <span>📢</span> Event Wrap-Up
                            </h3>
                            <p className="text-indigo-800 leading-relaxed text-lg italic opacity-80">
                                "{highlights.summary}"
                            </p>
                        </div>
                    )}

                    {/* Statistics */}
                    {highlights.statistics && (
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div className="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-blue-500 text-center">
                                <div className="text-3xl font-black text-gray-800 mb-1">{highlights.statistics.participants || 'N/A'}</div>
                                <div className="text-xs font-bold text-gray-400 uppercase tracking-widest">Participants</div>
                            </div>
                            <div className="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-purple-500 text-center">
                                <div className="text-3xl font-black text-gray-800 mb-1">{highlights.statistics.colleges || 'N/A'}</div>
                                <div className="text-xs font-bold text-gray-400 uppercase tracking-widest">Colleges</div>
                            </div>
                            <div className="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-orange-500 text-center">
                                <div className="text-3xl font-black text-gray-800 mb-1">{highlights.winners?.length || 0}</div>
                                <div className="text-xs font-bold text-gray-400 uppercase tracking-widest">Winners</div>
                            </div>
                            <div className="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-green-500 text-center">
                                <div className="text-3xl font-black text-gray-800 mb-1">{highlights.guests?.length || 0}</div>
                                <div className="text-xs font-bold text-gray-400 uppercase tracking-widest">Guests</div>
                            </div>
                        </div>
                    )}

                    {/* Winners Podium */}
                    {highlights.winners && highlights.winners.length > 0 && (
                        <div>
                            <h3 className="text-2xl font-bold text-gray-800 mb-8 text-center uppercase tracking-widest flex items-center justify-center gap-3">
                                <span className="text-3xl">🏆</span> Hall of Fame
                            </h3>
                            <div className="flex flex-wrap justify-center gap-6">
                                {highlights.winners.map((winner, idx) => (
                                    <div key={idx} className="bg-white rounded-2xl shadow-lg border border-yellow-100 p-6 w-full md:w-64 text-center transform hover:-translate-y-2 transition-transform">
                                        <div className="text-4xl mb-4 text-yellow-500">{idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : '🎖️'}</div>
                                        <div className="font-black text-gray-800 text-xl mb-1">{winner.name}</div>
                                        <div className="text-sm font-bold text-yellow-600 uppercase tracking-wider mb-2">{winner.position}</div>
                                        {winner.prize && <div className="text-gray-500 text-sm border-t border-gray-100 pt-2 mt-2">{winner.prize}</div>}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Guests & Sponsors Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {/* Guests */}
                        {highlights.guests && highlights.guests.length > 0 && (
                            <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                                <h3 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                    <span>🎙️</span> Honored Guests
                                </h3>
                                <ul className="space-y-4">
                                    {highlights.guests.map((guest, idx) => (
                                        <li key={idx} className="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                            <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                                {guest.name[0]}
                                            </div>
                                            <div>
                                                <div className="font-bold text-gray-800">{guest.name}</div>
                                                <div className="text-sm text-gray-500">{guest.role}</div>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {/* Sponsors */}
                        {highlights.sponsors && highlights.sponsors.length > 0 && (
                            <div className="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                                <h3 className="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                    <span>🤝</span> Our Sponsors
                                </h3>
                                <div className="flex flex-wrap gap-3">
                                    {highlights.sponsors.map((spr, idx) => (
                                        <div key={idx} className="bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-600 flex items-center gap-2">
                                            <span>{spr.name}</span>
                                            <span className="text-[10px] bg-gray-200 px-1.5 rounded uppercase tracking-wider">{spr.type}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                </div>
            )
            }

            <div className="flex justify-center pt-8 border-t border-gray-50">
                {event.status === 'open' ? (
                    <button
                        onClick={handleRegister}
                        disabled={loading || isRegistered}
                        className={`font-bold py-4 px-16 rounded-xl text-lg shadow-xl transition-all duration-200 transform ${isRegistered
                            ? 'bg-green-100 text-green-700 cursor-default px-12'
                            : 'bg-blue-600 hover:bg-blue-700 text-white hover:-translate-y-1 active:scale-95'
                            } ${loading ? 'opacity-70 cursor-not-allowed' : ''}`}
                    >
                        {loading ? (
                            <div className="flex items-center">
                                <svg className="animate-spin h-6 w-6 mr-3 text-white" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </div>
                        ) : (
                            isRegistered ? '✓ Registered' : 'Register Now'
                        )}
                    </button>
                ) : (
                    <div className="bg-gray-100 text-gray-500 font-bold py-4 px-16 rounded-xl text-lg flex items-center">
                        <span className="mr-2">🚫</span> Registration Closed
                    </div>
                )}
            </div>
        </div >
    );
};

export default EventDetails;
