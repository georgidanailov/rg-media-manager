import React, { useState } from 'react';
import axios from 'axios';

const FileUpload = ({ onUploadSuccess }) => {
    const [selectedFile, setSelectedFile] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadStatus, setUploadStatus] = useState('');

    const handleFileChange = (event) => {
        setSelectedFile(event.target.files[0]);
        setUploadProgress(0);
        setUploadStatus('');
    };

    const handleUpload = async (event) => {
        event.preventDefault();

        if (!selectedFile) {
            setUploadStatus('Please select a file to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('file', selectedFile);

        try {
            const response = await axios.post('http://localhost:9000/medias/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total
                    );
                    setUploadProgress(percentCompleted);
                },
            });

            if (response.status === 200) {
                setUploadStatus('File uploaded successfully!');
                setUploadProgress(0);

                // Trigger a refresh of the file list
                onUploadSuccess();
            } else {
                setUploadStatus('Failed to upload the file.');
                setUploadProgress(0);
            }
        } catch (error) {
            setUploadStatus('Error uploading the file. Please try again.');
            setUploadProgress(0);
        }
    };

    return (
        <div>
            <h2>Upload a File</h2>
            <form onSubmit={handleUpload}>
                <input type="file" onChange={handleFileChange} />
                <button type="submit">Upload</button>
            </form>
            {uploadProgress > 0 && (
                <div>
                    <p>Upload Progress: {uploadProgress}%</p>
                    <progress value={uploadProgress} max="100" />
                </div>
            )}
            {uploadStatus && <p>{uploadStatus}</p>}
        </div>
    );
};

export default FileUpload;
