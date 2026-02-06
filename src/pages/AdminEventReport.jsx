import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';
import { useNotification } from '../context/NotificationContext';

const AdminEventReport = () => {
    const { eventId } = useParams();
    const navigate = useNavigate();
    const { showNotification } = useNotification();
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    // Form State
    const [formData, setFormData] = useState({
        summary: '',
        winners: [],
        guests: [],
        statistics: { participants: '', colleges: '' },
        sponsors: []
    });

    const [eventName, setEventName] = useState('');

    useEffect(() => {
        fetchData();
    }, [eventId]);

    const fetchData = async () => {
        try {
            // Fetch Event Name
            const eventRes = await api.get('/admin/events.php');
            const event = eventRes.data.find(e => e.event_id == eventId);
            if (event) setEventName(event.event_name);

            // Fetch Existing Report
            const reportRes = await api.get(`/highlights_info.php?event_id=${eventId}`);
            if (reportRes.data) {
                setFormData({
                    summary: reportRes.data.summary || '',
                    winners: reportRes.data.winners || [],
                    guests: reportRes.data.guests || [],
                    statistics: reportRes.data.statistics || { participants: '', colleges: '' },
                    sponsors: reportRes.data.sponsors || []
                });
            }
        } catch (error) {
            console.error('Error fetching details', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            await api.post('/highlights_info.php', {
                event_id: eventId,
                ...formData
            });
            showNotification('Report saved successfully', 'success');
        } catch (error) {
            console.error('Save failed', error);
            showNotification('Failed to save report', 'error');
        } finally {
            setSaving(false);
        }
    };

    // Helper to add row
    const addItem = (field, template) => {
        setFormData(prev => ({ ...prev, [field]: [...prev[field], template] }));
    };

    // Helper to remove row
    const removeItem = (field, index) => {
        setFormData(prev => ({
            ...prev,
            [field]: prev[field].filter((_, i) => i !== index)
        }));
    };

    // Helper to update row
    const updateItem = (field, index, key, value) => {
        const newArr = [...formData[field]];
        newArr[index][key] = value;
        setFormData(prev => ({ ...prev, [field]: newArr }));
    };

    if (loading) return <div className="p-10 text-center">Loading...</div>;

    return (
        <div className="p-6 max-w-5xl mx-auto">
            <button
                onClick={() => navigate('/admin/events')}
                className="mb-4 text-blue-600 hover:underline flex items-center gap-2"
            >
                ← Back to Events
            </button>

            <div className="flex justify-between items-center mb-8">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800">Post-Event Report</h1>
                    <p className="text-gray-500 mt-1">For event: <span className="font-semibold text-blue-600">{eventName}</span></p>
                </div>
                <button
                    onClick={handleSave}
                    disabled={saving}
                    className="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all active:scale-95 disabled:opacity-50"
                >
                    {saving ? 'Saving...' : '💾 Save Report'}
                </button>
            </div>

            <div className="space-y-8">
                {/* 1. Summary */}
                <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">📢 Event Summary</h2>
                    <textarea
                        className="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                        rows="4"
                        placeholder="Summarize the event highlights, key outcomes, and success moments..."
                        value={formData.summary}
                        onChange={(e) => setFormData({ ...formData, summary: e.target.value })}
                    ></textarea>
                </div>

                {/* 2. Statistics */}
                <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">📊 Event Statistics</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Participants</label>
                            <input
                                type="text"
                                className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3"
                                placeholder="e.g. 500+"
                                value={formData.statistics.participants}
                                onChange={(e) => setFormData({
                                    ...formData,
                                    statistics: { ...formData.statistics, participants: e.target.value }
                                })}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Colleges/Teams</label>
                            <input
                                type="text"
                                className="w-full bg-gray-50 border border-gray-200 rounded-xl p-3"
                                placeholder="e.g. 25 Colleges"
                                value={formData.statistics.colleges}
                                onChange={(e) => setFormData({
                                    ...formData,
                                    statistics: { ...formData.statistics, colleges: e.target.value }
                                })}
                            />
                        </div>
                    </div>
                </div>

                {/* 3. Winners */}
                <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-xl font-bold text-gray-800">🏆 Winners List</h2>
                        <button
                            onClick={() => addItem('winners', { position: '1st Place', name: '', prize: '' })}
                            className="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 transition-colors"
                        >
                            + Add Winner
                        </button>
                    </div>
                    {formData.winners.length === 0 ? (
                        <p className="text-gray-400 italic text-sm">No winners added yet.</p>
                    ) : (
                        <div className="space-y-3">
                            {formData.winners.map((winner, idx) => (
                                <div key={idx} className="flex gap-4 items-start">
                                    <input
                                        placeholder="Rank/Position"
                                        className="w-1/4 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                        value={winner.position}
                                        onChange={(e) => updateItem('winners', idx, 'position', e.target.value)}
                                    />
                                    <input
                                        placeholder="Winner Name / Team"
                                        className="flex-1 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                        value={winner.name}
                                        onChange={(e) => updateItem('winners', idx, 'name', e.target.value)}
                                    />
                                    <input
                                        placeholder="Prize"
                                        className="w-1/4 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                        value={winner.prize}
                                        onChange={(e) => updateItem('winners', idx, 'prize', e.target.value)}
                                    />
                                    <button onClick={() => removeItem('winners', idx)} className="text-red-400 hover:text-red-600 p-2">×</button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* 4. Guests */}
                <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-xl font-bold text-gray-800">🎙️ Guests / Speakers</h2>
                        <button
                            onClick={() => addItem('guests', { name: '', role: '' })}
                            className="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 transition-colors"
                        >
                            + Add Guest
                        </button>
                    </div>
                    <div className="space-y-3">
                        {formData.guests.map((guest, idx) => (
                            <div key={idx} className="flex gap-4 items-start">
                                <input
                                    placeholder="Guest Name"
                                    className="flex-1 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                    value={guest.name}
                                    onChange={(e) => updateItem('guests', idx, 'name', e.target.value)}
                                />
                                <input
                                    placeholder="Role / Title"
                                    className="flex-1 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                    value={guest.role}
                                    onChange={(e) => updateItem('guests', idx, 'role', e.target.value)}
                                />
                                <button onClick={() => removeItem('guests', idx)} className="text-red-400 hover:text-red-600 p-2">×</button>
                            </div>
                        ))}
                    </div>
                </div>

                {/* 5. Sponsors */}
                <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-xl font-bold text-gray-800">🤝 Sponsors</h2>
                        <button
                            onClick={() => addItem('sponsors', { name: '', type: 'Main Sponsor' })}
                            className="text-sm bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-100 transition-colors"
                        >
                            + Add Sponsor
                        </button>
                    </div>
                    <div className="space-y-3">
                        {formData.sponsors.map((spr, idx) => (
                            <div key={idx} className="flex gap-4 items-start">
                                <input
                                    placeholder="Sponsor Name"
                                    className="flex-1 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                    value={spr.name}
                                    onChange={(e) => updateItem('sponsors', idx, 'name', e.target.value)}
                                />
                                <select
                                    className="w-1/3 bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm"
                                    value={spr.type}
                                    onChange={(e) => updateItem('sponsors', idx, 'type', e.target.value)}
                                >
                                    <option>Title Sponsor</option>
                                    <option>Main Sponsor</option>
                                    <option>Co-Sponsor</option>
                                    <option>Media Partner</option>
                                </select>
                                <button onClick={() => removeItem('sponsors', idx)} className="text-red-400 hover:text-red-600 p-2">×</button>
                            </div>
                        ))}
                    </div>
                </div>

            </div>
        </div>
    );
};

export default AdminEventReport;
