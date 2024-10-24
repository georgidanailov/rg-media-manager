import React, { useState } from 'react';
import axios from 'axios';

const FileUpload = () => {
    const [selectedFile, setSelectedFile] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadStatus, setUploadStatus] = useState('');
    const [mediaMetadata, setMediaMetadata] = useState(null); // State for media metadata

    const handleFileChange = (event) => {
        setSelectedFile(event.target.files[0]);
        setUploadProgress(0);
        setUploadStatus('');
        setMediaMetadata(null);
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
                    'Authorization': `Bearer ${localStorage.getItem('token')}` // Use the token from local storage
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

            } else {
                setUploadStatus('Failed to upload the file.');
            }
        } catch (error) {
            setUploadStatus('Error uploading the file. Please try again.');
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
            {mediaMetadata && (
                <div>
                    <h3>Media Metadata</h3>
                    <p>Filename: {mediaMetadata.filename}</p>
                    <p>File Size: {mediaMetadata.fileSize} bytes</p>
                    <p>File Type: {mediaMetadata.fileType}</p>
                    <p>Uploaded At: {new Date(mediaMetadata.createdAt).toLocaleString()}</p>
                </div>
            )}
        </div>
    );
};

export default FileUpload;
