import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('/api/token', {
                email,
                password,
            });

            // Store the token in localStorage
            localStorage.setItem('token', response.data.token);

            // Redirect the user to the dashboard after successful login
            navigate('/dashboard');
        } catch (err) {
            setError('Invalid credentials, please try again.');
        }
    };

    return (
        <div className="container vh-100 d-flex justify-content-center align-items-center">
            <div className="card p-4 shadow" style={{ width: '100%', maxWidth: '400px' }}>
                <h1 className="h3 mb-3 font-weight-normal text-center">Please sign in</h1>
                {error && <div className="alert alert-danger">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="mb-3">
                        <label htmlFor="inputEmail" className="form-label">Email</label>
                        <input
                            type="email"
                            className="form-control"
                            id="inputEmail"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            placeholder="Enter your email"
                        />
                    </div>

                    <div className="mb-3">
                        <label htmlFor="inputPassword" className="form-label">Password</label>
                        <input
                            type="password"
                            className="form-control"
                            id="inputPassword"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                            placeholder="Enter your password"
                        />
                    </div>

                    <button className="btn btn-primary w-100" type="submit">Sign in</button>
                </form>

                <p className="mt-3 text-center">
                    Don't have an account? <a href="/register">Register</a>
                </p>
            </div>
        </div>
    );
};

export default Login;
