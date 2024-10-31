import React from 'react';

const FileTable = ({ files }) => {
    // Function to convert file size from bytes to megabytes
    const formatSize = (sizeInBytes) => {
        const sizeInMB = sizeInBytes / (1024 * 1024);
        return `${sizeInMB.toFixed(2)} MB`;
    };

    return (
        <div className="table-responsive">
            <table className="table table-striped table-hover table-bordered">
                <thead className="table-light">
                <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded Date</th>
                    <th>Size</th>
                    <th>Preview</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {files.length > 0 ? (
                    files.map((file) => (
                        <tr key={file.id}>
                            <td>{file.name}</td>
                            <td>{file.file}</td>
                            <td>{new Date(file.uploadedDate).toLocaleString()}</td>
                            <td>{formatSize(file.size)}</td>
                            <td>
                                {file.preview ? (
                                    <img
                                        src={`http://localhost:9000${file.preview}`} // Ensure the preview path is correct
                                        alt={file.name}
                                        style={{ width: '100px', height: 'auto' }}
                                    />
                                ) : (
                                    'No preview available'
                                )}
                            </td>
                            <td>
                                <button className="btn btn-success btn-sm me-2">Edit</button>
                                <button className="btn btn-danger btn-sm me-2">Delete</button>
                                <a
                                    href={`http://localhost:9000${file.downloadUrl}`}
                                    className="btn btn-primary btn-sm"
                                >
                                    Download
                                </a>
                            </td>
                        </tr>
                    ))
                ) : (
                    <tr>
                        <td colSpan="6" className="text-center">
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
