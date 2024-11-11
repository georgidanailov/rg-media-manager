import React, { useState } from 'react';
import axios from 'axios';
import '../FileUpload.css';

const FileUpload = ({ onUploadSuccess }) => {
    const [selectedFile, setSelectedFile] = useState(null);
    const [tags, setTags] = useState('');
    const [uploadProgress, setUploadProgress] = useState(0);
    const [uploadStatus, setUploadStatus] = useState('');

    const handleFileChange = (event) => {
        setSelectedFile(event.target.files[0]);
        setUploadProgress(0);
        setUploadStatus('');
    };

    const handleTagsChange = (event) => {
        setTags(event.target.value);
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
                setUploadStatus('File uploaded successfully and sent for processing!');
                setUploadProgress(0);

                const mediaId = response.data.mediaId;
                await handleAddTags(mediaId);

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

    const handleAddTags = async (mediaId) => {
        const intMediaId = parseInt(mediaId, 10);
        const tagList = tags.split(',').map(tag => tag.trim().toLowerCase());

        if (tagList.length > 0) {
            try {
                const response = await axios.post(`http://localhost:9000/media/${intMediaId}/tags`, {
                    tags: tagList,
                }, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Content-Type': 'application/json',
                    },
                });

                if (response.status === 200) {
                    setUploadStatus('Tags added successfully.');
                } else {
                    setUploadStatus('Failed to add tags.');
                }
            } catch (error) {
                console.error('Tag addition error:', error);
                setUploadStatus('Error adding tags. Please try again.');
            }
        }
    };


    return (
        <div className="upload-container">
            <h2 className="upload-title">Upload a File</h2>
            <form onSubmit={handleUpload}>
                <input
                    type="file"
                    className="upload-form-file-input"
                    onChange={handleFileChange}
                />
                <input
                    type="text"
                    placeholder="Enter tags, separated by commas"
                    className="upload-form-tags-input"
                    value={tags}
                    onChange={handleTagsChange}
                />
                <button type="submit" className="upload-form-button">Upload</button>
            </form>
            {uploadProgress > 0 && (
                <div>
                    <p className="upload-progress-text">Upload Progress: {uploadProgress}%</p>
                    <progress className="upload-progress-bar" value={uploadProgress} max="100"/>
                </div>
            )}
            {uploadStatus && <p className="upload-status">{uploadStatus}</p>}
        </div>

    );
};

export default FileUpload;
