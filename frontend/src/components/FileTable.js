import React, {useState} from 'react';
import {jwtDecode} from "jwt-decode";
import axios from "axios";


const token = localStorage.getItem('token');
let role;
if (token){
    const decoded = jwtDecode(token);
    role = decoded?.roles?.[0];
}
else {
    console.log('no token found');
}
const FileTable = ({ files, onDeleteSuccess }) => {

    const [selectedFiles, setSelectedFiles] = useState({});

    const allSelected = Object.values(selectedFiles).every(isSelected => isSelected);
    const handleCheckboxChange = (fileId) => {
        setSelectedFiles(prev => ({
            ...prev,
            [fileId]: !prev[fileId]
        }));
    };

    const handleSelectAll = () => {
        const newSelectedFiles = {};
        files.forEach(file=> {
            newSelectedFiles[file.id] = !allSelected;
        });
        setSelectedFiles(newSelectedFiles);
    }

    const handleDownloadSelected = async () => {
        const selectedFileIds = Object.keys(selectedFiles).filter(fileId => selectedFiles[fileId]);

        if (selectedFileIds.length === 0) {
            alert("No Files selected for download.");
            return;
        }

        try {
            const response = await axios.post('http://localhost:9000/media/download-multiple',
                {files: selectedFileIds},
                {headers: {
                            Authorization: `Bearer ${token}`,
                    },
                responseType: 'blob',
                });
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');

            link.href = url;
            link.download = 'files.zip';
            document.body.appendChild(link);
            link.click();
            link.parentNode.removeChild(link);
        } catch (error) {
            console.error('Error downloading files:', error);
            alert('An error occurred while trying to download the files.');
        }
    };

    // Function to convert file size from bytes to megabytes
    const formatSize = (sizeInBytes) => {
        const sizeInMB = sizeInBytes / (1024 * 1024);
        return `${sizeInMB.toFixed(2)} MB`;
    };

    const onDelete = async (fileId) => {
        try {
            const response = await axios.delete(`http://localhost:9000/media/${fileId}/delete`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            if (response.status === 200) {
                console.log ('File Successfully Deleted');
                alert('File Successfully Deleted');
                onDeleteSuccess();
            }
        } catch (error) {
            console.error('Error deleting file:', error);
            alert('An error has occured while trying to delete the file.');
        }
    }

    const onDownload = async (fileId, fileName, fileExtension) => {
        try {
            const response = await axios.get(`http://localhost:9000/media/${fileId}/download`, {
                responseType: 'blob',
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            const type = response.data.type
            const blob = new Blob([response.data], { type: type });
            const downloadUrl = window.URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = fileName + '.' + fileExtension;

            document.body.appendChild(link);
            link.click();

            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

        } catch (error) {
            console.error("Download failed:", error);
        }
    };

    return (
        <div className="table-responsive">

            <button className="btn btn-success mb-3" onClick={handleSelectAll}>
                {allSelected ? "Deselect All" : "Select All"}
            </button>
            <button className="btn btn-success mb-3 ms-3" onClick={handleDownloadSelected}>Download Selected</button>

            <table className="table table-striped table-hover table-bordered">
                <thead className="table-light">
                <tr>
                    <th>Select</th>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded Date</th>
                    {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                        <th>Author Name</th>
                    ) : null}
                    <th>Size</th>
                    {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                        <th>Tags</th>
                    ) : null}
                    <th>Preview</th>
                    {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                        <th>Versions</th>
                    ) : null}
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {files.length > 0 ? (
                    files.map((file) => (
                        <tr key={file.id}>
                            <td><input className="form-check-input" id="custom-checkbox" checked={selectedFiles[file.id] || false} onChange={() => handleCheckboxChange(file.id)} type="checkbox" style={{width: "20px", height: "20px"}}/></td>
                            <td>{file.name}</td>
                            <td>{file.file}</td>
                            <td>{new Date(file.uploadedDate).toLocaleString()}</td>
                            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                            <td>{file.author}</td>
                            ) : null}
                            <td>{formatSize(file.size)}</td>
                            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                            <td>{file.tags.map(tag => tag.name).join(', ') }</td>
                            ) : null}
                            <td>
                                {file.preview ? (
                                    <img
                                        src={`http://localhost:9000${file.preview}`}
                                        alt={file.name}
                                        style={{ width: '100px', height: 'auto' }}
                                    />
                                ) : (
                                    'No preview available'
                                )}
                            </td>
                            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                            <td>{file.fileVersions}</td>
                            ) : null}
                            <td>
                                <button className="btn btn-success btn-sm me-2" >Edit</button>
                                <button className="btn btn-danger btn-sm me-2" onClick={() => onDelete(file.id)}>Delete</button>
                                <button
                                    onClick={() => onDownload(file.id, file.name, file.extension)}
                                    className="btn btn-primary btn-sm "
                                >
                                    Download
                                </button>
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
