import React, { useState, useEffect } from 'react';
import FileTable from './FileTable';
import SearchFilter from './SearchFilter';
import FileUpload from "./FileUpload";
import axios from 'axios';

const Dashboard = () => {
    const [files, setFiles] = useState([]);
    const [refreshFiles, setRefreshFiles] = useState(false); // State to trigger a refresh after upload
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);

    const fetchAllFiles = async () => {
        try {
            const response = await axios.get('http://localhost:9000/media/filter', {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                },
                params: {
                    page, // Send the current page number to the backend
                }
            });

            if (response.data && response.data.data) {
                setFiles(response.data.data);
                setTotalPages(Math.ceil(response.data.totalItems / response.data.itemsPerPage));
            } else {
                setFiles([]);
            }
        } catch (error) {
            console.error('Error fetching files:', error);
        }
    };

    // Function to handle the search
    const handleSearch = async (filters) => {
        try {
            const { name, type, size } = filters;
            const response = await axios.get('http://localhost:9000/media/filter', {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                },
                params: {
                    name: name || undefined,
                    type: type || undefined,
                    size: size || undefined,
                    page, // Send the current page number with the search
                }
            });

            if (response.data && response.data.data) {
                setFiles(response.data.data);
                setTotalPages(Math.ceil(response.data.totalItems / response.data.itemsPerPage));
            } else {
                setFiles([]);
            }
        } catch (error) {
            console.error('Error fetching filtered files:', error);
        }
    };

    // Function to handle successful file upload and refresh the file list
    const handleUploadSuccess = () => {
        setRefreshFiles(!refreshFiles); // Trigger a refresh by toggling state
    };

    // Function to handle page change
    const handlePageChange = (newPage) => {
        setPage(newPage);
    };

    // UseEffect to fetch all files on initial page load and when refreshFiles changes
    useEffect(() => {
        fetchAllFiles();
    }, [refreshFiles, page]);

    return (
        <div className="container-fluid">
            <div className="row">
                {/* Sidebar */}
                <div className="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar">
                    <div className="d-flex flex-column p-3">
                        <h4 className="mb-4">Library</h4>
                        <ul className="nav flex-column">
                            <li className="nav-item mb-2">
                                <a href="/frontend/public" className="nav-link text-dark">
                                    <i className="bi bi-folder-fill me-2"></i>Folder 1
                                </a>
                            </li>
                            <li className="nav-item mb-2">
                                <a href="/frontend/public" className="nav-link text-dark">
                                    <i className="bi bi-folder-fill me-2"></i>Folder 2
                                </a>
                            </li>
                            <li className="nav-item mb-2">
                                <a href="/frontend/public" className="nav-link text-dark">
                                    <i className="bi bi-folder-fill me-2"></i>Folder 3
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                {/* Main Content */}
                <div className="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div className="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h2>Your Uploaded Files</h2>
                    </div>
                    <SearchFilter onSearch={handleSearch} />
                    <FileTable files={files} /> {/* Pass the files to FileTable */}
                    <div className="pagination">
                        <button
                            disabled={page === 1}
                            onClick={() => handlePageChange(page - 1)}
                            className="btn btn-primary me-2"
                        >
                            Previous
                        </button>
                        <span>Page {page} of {totalPages}</span>
                        <button
                            disabled={page === totalPages}
                            onClick={() => handlePageChange(page + 1)}
                            className="btn btn-primary ms-2"
                        >
                            Next
                        </button>
                    </div>
                    <FileUpload onUploadSuccess={handleUploadSuccess} /> {/* Pass handleUploadSuccess to refresh after upload */}
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
