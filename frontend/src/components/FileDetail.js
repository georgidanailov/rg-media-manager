import React, { useEffect, useState } from 'react';
import axios from 'axios';
import '../FileDetail.css';
import Loader from "./Loader";


const FileDetail = ({ fileId, onClose }) => {
    const [fileDetails, setFileDetails] = useState(null);
    const [fileVersions, setFileVersions] = useState([]);
    const [newVersion, setNewVersion] = useState(null);



    useEffect(() => {
        const fetchFileDetails = async () => {
            try {
                const response = await axios.get(`http://127.0.0.1:9000/media/${fileId}`, {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });
                setFileDetails(response.data.media);
                setFileVersions(response.data.versions)
            } catch (error) {
                console.error('Error fetching file details:', error);
            }
        };

        fetchFileDetails();
    }, [fileId]);

    if (!fileDetails) return <Loader/>;

    let parentId;

    if (fileDetails.parent == null){
        parentId = fileDetails.id;
    } else {
        parentId = fileDetails.parent.id;
    }

    const handleFileChange = (e) => {
        setNewVersion(e.target.files[0]);
    };

    const handleUpload = async () => {
        if (!newVersion) {
            alert('Please select a file to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('file', newVersion);

        try {
            await axios.post(`http://127.0.0.1:9000/media/${parentId}/upload`, formData, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'multipart/form-data',
                },
            });
            alert('New version uploaded successfully!');
            setNewVersion(null);
        } catch (error) {
            console.error('Error uploading new version:', error);
            alert('Failed to upload new version.');
        }
    };

    return (
        <div className="file-detail-container">
            <div className="file-detail-header">
                <h3>{fileDetails.file_name}</h3>
                <button className="close-btn" onClick={onClose}>Back</button>
            </div>
            <div className="file-detail-content">
                {fileDetails.file_type === 'video' ? <video
                        width="640"
                        height="360"
                        controls
                        onError={() => console.error("Failed to load video.")}
                    >
                        <source src={`http://127.0.0.1:9000/uploads${fileDetails.storage_path}`} type="video/mp4"/>
                        Your browser does not support the video tag.
                    </video> :
                    <img src={`http://127.0.0.1:9000/${fileDetails.thumbnail_path}`} alt=""/>}
                <div className="file-info">
                    <p><strong>Type:</strong> {fileDetails.file_type}</p>
                    <p><strong>Size:</strong> {(fileDetails.file_size / 1024 / 1024).toFixed(2)} MB</p>
                    <p><strong>Uploaded By:</strong> {fileDetails.user.name}</p>
                    <p><strong>Upload Date:</strong> {fileDetails.created_at.substring(0, 10)}</p>
                    <button
                        className="download-btn"
                    >
                        Download Current Version
                    </button>
                </div>
            </div>

            {fileVersions.length > 0 && (
                <div className="file-versions">
                    <h4>File Versions</h4>
                    {fileVersions.map((version) => (
                        <div key={version.id} className="version-item">
                            <p><strong>Version {version.version}</strong> - Uploaded
                                on {version.created_at.substring(0, 10)}</p>
                            <button
                                className="download-btn"
                            >
                                Download Version {version.version}
                            </button>
                        </div>
                    ))}
                </div>
            )}
            <div className="upload-new-version">
                <h4>Upload New Version</h4>
                <input
                    type="file"
                    onChange={handleFileChange}
                    className="file-input form-control-file"
                />
                <button
                    className="upload-btn btn btn-success"
                    onClick={handleUpload}
                >
                    Upload New Version
                </button>
            </div>
        </div>
    );
};

export default FileDetail;


