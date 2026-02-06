import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api, { BASE_URL } from '../services/api';
import { useNotification } from '../context/NotificationContext';

const AdminGallery = () => {
    const { eventId } = useParams();
    const navigate = useNavigate();
    const { showNotification, showConfirm } = useNotification();
    const [images, setImages] = useState([]);
    const [uploading, setUploading] = useState(false);
    const [eventName, setEventName] = useState('');

    useEffect(() => {
        fetchGallery();
        fetchEventDetails();
    }, [eventId]);

    const fetchEventDetails = async () => {
        try {
            const response = await api.get('/admin/events.php');
            const event = response.data.find(e => e.event_id == eventId);
            if (event) setEventName(event.event_name);
        } catch (error) {
            console.error('Error fetching event details', error);
        }
    };

    const fetchGallery = async () => {
        try {
            const response = await api.get(`/gallery_api.php?event_id=${eventId}&action=list`);
            setImages(response.data);
        } catch (error) {
            console.error('Error fetching gallery:', error);
            showNotification('Failed to load gallery', 'error');
        }
    };

    const handleFileUpload = async (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        const formData = new FormData();
        formData.append('event_id', eventId);
        files.forEach((file) => {
            formData.append('images[]', file);
        });

        setUploading(true);
        try {
            await api.post('/gallery_api.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            showNotification('Images uploaded successfully', 'success');
            fetchGallery();
        } catch (error) {
            console.error('Upload failed:', error);
            showNotification('Upload failed', 'error');
        } finally {
            setUploading(false);
        }
    };

    const handleDelete = (imageId) => {
        showConfirm(
            'Delete Image',
            'Are you sure you want to delete this image?',
            async () => {
                try {
                    await api.delete(`/gallery_api.php?id=${imageId}`);
                    showNotification('Image deleted successfully', 'success');
                    fetchGallery();
                } catch (error) {
                    showNotification('Failed to delete image', 'error');
                }
            }
        );
    };

    return (
        <div className="p-6">
            <button
                onClick={() => navigate('/admin/events')}
                className="mb-4 text-blue-600 hover:underline flex items-center gap-2"
            >
                ← Back to Events
            </button>

            <div className="flex justify-between items-center mb-8">
                <div>
                    <h1 className="text-3xl font-bold text-gray-800">Event Gallery</h1>
                    <p className="text-gray-500 mt-1">Manage photos for <span className="font-semibold text-blue-600">{eventName}</span></p>
                </div>

                <div className="relative">
                    <input
                        type="file"
                        multiple
                        accept="image/*"
                        onChange={handleFileUpload}
                        className="hidden"
                        id="gallery-upload"
                        disabled={uploading}
                    />
                    <label
                        htmlFor="gallery-upload"
                        className={`bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl cursor-pointer shadow-lg transition-all flex items-center gap-2 ${uploading ? 'opacity-50 cursor-not-allowed' : ''}`}
                    >
                        {uploading ? 'Uploading...' : '📸 Upload Photos'}
                    </label>
                </div>
            </div>

            {images.length === 0 ? (
                <div className="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <div className="text-6xl mb-4">🖼️</div>
                    <h3 className="text-xl font-bold text-gray-400">No photos yet</h3>
                    <p className="text-gray-400 mt-2">Upload photos to showcase highlights from this event.</p>
                </div>
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    {images.map((img) => (
                        <div key={img.id} className="group relative bg-white p-2 rounded-xl shadow-sm hover:shadow-md transition-all">
                            <div className="aspect-square rounded-lg overflow-hidden mb-2 relative">
                                <img
                                    src={`${BASE_URL}/${img.image_path}`}
                                    alt="Gallery"
                                    className="w-full h-full object-cover transition-transform group-hover:scale-105"
                                />
                                <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button
                                        onClick={() => handleDelete(img.id)}
                                        className="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-transform hover:scale-110"
                                        title="Delete Image"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            <p className="text-xs text-gray-400 text-center">Uploaded {new Date(img.uploaded_at).toLocaleDateString()}</p>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default AdminGallery;
