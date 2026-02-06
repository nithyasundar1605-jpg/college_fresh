import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import api from '../services/api';
import { useNotification } from '../context/NotificationContext';

const Register = () => {
  const { showNotification } = useNotification();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    college_name: '',
    phone_number: '',
    address: '',
    course_name: '',
    year_of_study: ''
  });
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    if (formData.password !== formData.confirmPassword) {
      showNotification('Passwords do not match', 'error');
      return;
    }

    try {
      await api.post('/auth/register.php', {
        name: formData.name,
        email: formData.email,
        password: formData.password,
        college_name: formData.college_name,
        phone_number: formData.phone_number,
        address: formData.address,
        course_name: formData.course_name,
        year_of_study: formData.year_of_study
      });
      showNotification('Registration successful! Please login.', 'success');
      setTimeout(() => navigate('/login'), 2000);
    } catch (err) {
      console.error('Registration Error:', err);
      // ... error handling
      let errorMessage = 'Registration failed. Server might be unreachable.';
      if (err.response && err.response.data) {
        errorMessage = typeof err.response.data === 'string'
          ? err.response.data
          : (err.response.data.message || JSON.stringify(err.response.data));
      } else if (err.message) {
        errorMessage = 'Error: ' + err.message;
      }
      setError(errorMessage);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl">
        <h2 className="text-2xl font-bold mb-6 text-center text-gray-800">Student Registration</h2>
        {error && <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{error}</div>}
        <form onSubmit={handleRegister} className="space-y-4">

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
              <input
                type="text"
                name="name"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.name}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Email</label>
              <input
                type="email"
                name="email"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">College Name</label>
              <input
                type="text"
                name="college_name"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.college_name}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
              <input
                type="text"
                name="phone_number"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.phone_number}
                onChange={handleChange}
                required
              />
            </div>
          </div>

          <div>
            <label className="block text-gray-700 text-sm font-bold mb-2">Address</label>
            <textarea
              name="address"
              className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
              value={formData.address}
              onChange={handleChange}
              rows="2"
              required
            ></textarea>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Course Name</label>
              <input
                type="text"
                name="course_name"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.course_name}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Year of Study</label>
              <select
                name="year_of_study"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.year_of_study}
                onChange={handleChange}
                required
              >
                <option value="">Select Year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
                <option value="5">5th Year</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Password</label>
              <input
                type="password"
                name="password"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.password}
                onChange={handleChange}
                required
              />
            </div>
            <div>
              <label className="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
              <input
                type="password"
                name="confirmPassword"
                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                value={formData.confirmPassword}
                onChange={handleChange}
                required
              />
            </div>
          </div>
          <button
            type="submit"
            className="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition duration-300"
          >
            Register
          </button>
        </form>
        <div className="mt-4 text-center">
          <Link to="/login" className="text-blue-600 hover:text-blue-800 text-sm">
            Already have an account? Login here
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Register;