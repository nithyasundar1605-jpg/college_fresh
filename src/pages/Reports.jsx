import { BASE_URL } from '../services/api';

const Reports = () => {
    const handleDownload = (endpoint) => {
        window.open(`${BASE_URL}/admin/${endpoint}`, '_blank');
    };

    return (
        <div>
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-800">Reports</h1>
                <p className="text-gray-600 mt-2">Analytical insights and detailed event reports.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-white p-8 rounded-xl shadow-md border border-gray-100 text-center">
                    <div className="text-5xl mb-4">📊</div>
                    <h3 className="text-xl font-bold text-gray-800 mb-2">Event Participation</h3>
                    <p className="text-gray-500 mb-6">Detailed breakdown of student registration and attendance per event.</p>
                    <button
                        onClick={() => handleDownload('participation_report.php')}
                        className="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors"
                    >
                        Download Report
                    </button>
                </div>
                <div className="bg-white p-8 rounded-xl shadow-md border border-gray-100 text-center">
                    <div className="text-5xl mb-4">🏆</div>
                    <h3 className="text-xl font-bold text-gray-800 mb-2">Certification Summary</h3>
                    <p className="text-gray-500 mb-6">Overview of all certificates issued to date across departments.</p>
                    <button
                        onClick={() => handleDownload('certification_report.php')}
                        className="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors"
                    >
                        Download Report
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Reports;
