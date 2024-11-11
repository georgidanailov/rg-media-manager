import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import backgroundImage from '../images/login-bg.jpg';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);


    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true); // Start loading

        try {
            const response = await axios.post('http://localhost:9000/login', {
                email,
                password,
            });

            // Store the token in localStorage
            localStorage.setItem('token', response.data.token);

            // Redirect the user to the dashboard after successful login
            navigate('/dashboard');
        } catch (err) {
            setError('Invalid credentials, please try again.');
        } finally {
            setLoading(false); //Stop loading
        }
    };

    return (
        <div
            className="login"
            style={{
                backgroundImage: `url(${backgroundImage})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                backgroundAttachment: 'fixed',
            }}
        >
            <div className="login-container">
                <h2 className="login__title">Please sign in</h2>
                {error && <div className="error">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="login__box">
                        <label htmlFor="inputEmail" className="login__label">Email</label>
                        <input
                            type="email"
                            className="login__input"
                            id="inputEmail"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            placeholder="Enter your email"
                        />
                    </div>

                    <div className="login__box">
                        <label htmlFor="inputPassword" className="login__label">Password</label>
                        <input
                            type="password"
                            className="login__input"
                            id="inputPassword"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                            placeholder="Enter your password"
                        />
                    </div>

                    <button className="login__button" type="submit" disabled={loading}>
                        {loading ? 'Signing in...': 'Sign in'}
                    </button>
                </form>

                <p className="login__register">
                    Don't have an account? <a href="/register">Register</a>
                </p>
            </div>
        </div>
    );
};

export default Login;
