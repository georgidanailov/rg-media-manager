import React, { useState, useEffect } from 'react';
import FileTable from './FileTable';
import SearchFilter from './SearchFilter';
import FileUpload from "./FileUpload";
import axios from 'axios';
import {useNavigate} from "react-router-dom";
import {jwtDecode} from "jwt-decode";
import Reports from './Reports';
import FileDetail from "./FileDetail";
import "../Pagination.css";
import Loader from "./Loader";
import "../Loader.css";


const token = localStorage.getItem('token');
let role;
if (token){
    const decoded = jwtDecode(token);
    role = decoded?.roles?.[0];
}
else {
    console.log('no token found');
}

const Dashboard = () => {
    const [files, setFiles] = useState([]);
    const [refreshFiles, setRefreshFiles] = useState(false);
    const [page, setPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const navigate = useNavigate();
    const [activeSection, setActiveSection] = useState('files');
    const [selectedFileId, setSelectedFileId] = useState(null);
    const [loading, setLoading] = useState(false);


    const handleViewDetails = (fileId) => {
        setSelectedFileId(fileId);
    };

    const handleCloseDetail = () => {
        setSelectedFileId(null);
    };

    const handleLogout = () => {
        navigate('/logout');
    };

    const fetchAllFiles = async () => {
        setLoading(true);
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
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = async (filters) => {
        try {
            const { name, type, size, user, tag, date } = filters;
            const response = await axios.get('http://localhost:9000/media/filter', {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                },
                params: {
                    name: name || undefined,
                    type: type || undefined,
                    size: size || undefined,
                    user: user || undefined,
                    tag: tag || undefined,
                    date: date || undefined,
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
                <div className="px-md-4">
                    {loading ? (
                        <Loader/>
                    ) : (
                        <>
                            <div
                                className="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                                <div className="d-flex flex-row header-div">
                                    <h2><a href="#"
                                           className={`links text-decoration-none link-dark me-4 ${activeSection === "files" ? "active" : ""}`}
                                           onClick={() => setActiveSection('files')}>Your Uploaded Files</a></h2>
                                    {role === "ROLE_ADMIN" ? (
                                        <h2><a href="#"
                                               className={`text-decoration-none link-dark ${activeSection === "reports" ? "active" : ""}`}
                                               onClick={() => setActiveSection('reports')}>Reports</a></h2>
                                    ) : null}
                                </div>
                                <button onClick={handleLogout} className="btn btn-success">Logout</button>
                            </div>

                            {selectedFileId ? (
                                <FileDetail fileId={selectedFileId} onClose={handleCloseDetail}/>
                            ) : (

                                activeSection === "files" ? (
                                    <>
                                        <SearchFilter onSearch={handleSearch}/>
                                        <FileTable files={files} onViewDetails={handleViewDetails}
                                                   onDeleteSuccess={handleUploadSuccess}/>
                                        <div className="pagination">
                                            <button
                                                disabled={page === 1}
                                                onClick={() => handlePageChange(page - 1)}
                                                className="pagination-button"
                                            >
                                                Previous
                                            </button>
                                            <span className="pagination-info">Page {page} of {totalPages}</span>
                                            <button
                                                disabled={page === totalPages}
                                                onClick={() => handlePageChange(page + 1)}
                                                className="pagination-button"
                                            >
                                                Next
                                            </button>
                                        </div>
                                        <FileUpload onUploadSuccess={handleUploadSuccess}/>
                                    </>
                                ) : (
                                    <Reports/>
                                )
                            )}
                        </>

                        )}

                </div>
            </div>
        </div>
    );
};

export default Dashboard;
