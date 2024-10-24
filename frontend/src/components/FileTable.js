import React, { useEffect, useState } from 'react';
import axios from 'axios';

// Utility function to format bytes to MB
const formatBytesToMB = (bytes) => {
    if (bytes === 0) return '0 MB';
    const formattedSize = (bytes / (1024 * 1024)).toFixed(2); // Convert bytes to MB and format to 2 decimal places
    return `${formattedSize} MB`;
};

const FileTable = () => {
    const [files, setFiles] = useState([]); // Initialize files as an empty array
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchFiles = async () => {
            try {
                const response = await axios.get('http://localhost:9000/media/filter', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    }
                });

                // Check if the response and data exist
                if (Array.isArray(response.data)) {
                    setFiles(response.data); // Directly set files to the array
                } else {
                    setError('No files found.');
                }
            } catch (err) {
                setError('Failed to fetch files.');
            } finally {
                setLoading(false);
            }
        };

        fetchFiles();
    }, []);

    if (loading) {
        return <p>Loading files...</p>;
    }

    return (
        <div className="table-responsive">
            <table className="table table-striped table-hover table-bordered">
                <thead className="table-light">
                <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded Date</th>
                    <th>Size</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {error && (
                    <tr>
                        <td colSpan="5" className="text-center text-danger">
                            {error}
                        </td>
                    </tr>
                )}
                {files.length > 0 ? (
                    files.map((file) => (
                        <tr key={file.id}>
                            <td>{file.name}</td>
                            <td>{file.file}</td>
                            <td>{new Date(file.uploadedDate).toLocaleString()}</td>
                            <td>{formatBytesToMB(file.size)}</td>
                            <td>
                                <button className="btn btn-success btn-sm me-2">Edit</button>
                                <button className="btn btn-danger btn-sm me-2">Delete</button>
                                <a href={file.downloadUrl} className="btn btn-primary btn-sm">Download</a>
                            </td>
                        </tr>
                    ))
                ) : (
                    <tr>
                        <td colSpan="5" className="text-center">
                            No files found.
                        </td>
                    </tr>
                )}
                </tbody>
            </table>
        </div>
    );
};

export default FileTable;
