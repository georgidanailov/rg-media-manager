import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import backgroundImage from '../images/login-bg.jpg'; // Adjust path based on your project structure

const Register = () => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [successMessage, setSuccessMessage] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('http://localhost:9000/register', {
                name,
                email,
                password,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            setSuccessMessage('Registration successful! Please log in.');
            setError('');

            setTimeout(() => {
                navigate('/login');
            }, 2000);
        } catch (err) {
            setError('Registration failed. Please check your details and try again.');
            setSuccessMessage('');
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
                <h2 className="login__title">Register</h2>

                {error && <div className="error">{error}</div>}
                {successMessage && <div className="success">{successMessage}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="login__box">
                        <label htmlFor="inputName" className="login__label">Name</label>
                        <input
                            type="text"
                            className="login__input"
                            id="inputName"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            required
                            placeholder="Enter your name"
                        />
                    </div>

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

                    <button className="login__button" type="submit">Register</button>
                </form>

                <p className="login__register">
                    Already have an account? <a href="/login">Log in</a>
                </p>
            </div>
        </div>
    );
};

export default Register;
