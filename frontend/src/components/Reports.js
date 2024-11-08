import React, {useEffect, useState} from "react";
import TotalStorageChart from "./TotalStorageChart";
import axios, {options} from 'axios';
import '../Charts.css';
import FileTypesChart from "./FileTypesChart";
import ActivityLogTable from "./LogTable";

const Reports = () => {

    const [storageData, setStorageData] = useState([]);
    const [fileTypeData, setFileTypeData] = useState([]);
    const [logData, setLogData] = useState([]);
    const [users, setUsers] = useState([]);
    const [selectedUser, setSelectedUser] = useState('');
    const [currentPage, setCurrentPage] = useState(1);  // Track current page
    const [totalPages, setTotalPages] = useState(1);    // Track total pages
    const [logsPerPage] = useState(10);  // Number of logs per page

    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const response = await axios.get('http://127.0.0.1:9000/users', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });

                setUsers(response.data);
            } catch (error) {
                console.error('Error fetching users:', error);
            }
        };
        fetchUsers();
    }, []);

    useEffect(() => {
        const fetchLogData = async () => {
            try {
                const response = await axios.get('http://127.0.0.1:9000/report/activity-logs', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                    params: {
                        user_id: selectedUser || '',  // Filter by user if selected
                        page: currentPage,
                        perPage: logsPerPage,
                    },
                });

                setLogData(response.data.data);
                setTotalPages(response.data.pagination.total_pages);  // Set total pages for pagination
            } catch (error) {
                console.error('Error fetching log data:', error);
            }
        };
        fetchLogData();
    }, [currentPage, selectedUser, logsPerPage]);

    const handleUserChange = (event) => {
        setSelectedUser(event.target.value || null);
    };

    const handleNextPage = () => {
        if (currentPage < totalPages) {
            setCurrentPage(currentPage + 1);
        }
    };

    const handlePrevPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    const handlePageClick = (page) => {
        setCurrentPage(page);
    };

    useEffect(() => {
        const fetchFileTypeData = async () => {
            try{
                const response = await axios.get('http://127.0.0.1:9000/report/file-types-per-user', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });

                setFileTypeData(response.data);
            }catch (error) {
                console.error("Error fetching file type data:", error);
            }
        };

        fetchFileTypeData();
    }, []);

    useEffect(() => {
        const fetchStorageData = async () => {
            try {
                const response = await axios.get('http://127.0.0.1:9000/report/storage-per-user', {
                    headers: {
                        Authorization: `Bearer ${localStorage.getItem('token')}`,
                    },
                });

                setStorageData(response.data);
            } catch (error) {
                console.error('Error fetching storage data:', error);
            }
        };

        fetchStorageData();
    }, []);

    const downloadCSV = () => {
        const headers = Object.keys(logData[0] || {}).join(',');

        const rows = logData.map(row => {
            return Object.values(row).map(value => {
                if (typeof value === 'object' && value !== null) {
                    return `"${JSON.stringify(value)}"`;
                } else {
                    return `"${value}"`;
                }
            }).join(',');
        });

        const csvContent = [headers, ...rows].join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv' }); // Create a Blob from the CSV content
        const url = URL.createObjectURL(blob); // Create the URL from the blob after defining it


        const link = document.createElement('a');
        link.href = url;
        link.download = 'activity_logs.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    return (
        <div className={'main-container'}>
            <div className={'charts-row'}>
                <div>
                    <TotalStorageChart storageData={storageData}/>
                </div>
                <div>
                    <FileTypesChart fileData={fileTypeData}/>
                </div>
            </div>
            <div className={'logTable'}>
                <h3>Activity Logs</h3>
                <div className={'log-filter-download'}>
                    <select className={'form-select mb-3'} id="userFilter" value={selectedUser || ""}
                            onChange={handleUserChange}>
                        <option value="">All Users</option>
                        {users.map(user => (
                            <option key={user.id} value={user.id}>{user.email}</option>
                        ))}
                    </select>
                    <button onClick={downloadCSV} className={'btn btn-success mb-3'}>Export to CSV</button>
                </div>
                <ActivityLogTable logData={logData}/>
                <nav aria-label="Page navigation">
                    <ul className="pagination justify-content-center">
                        <li className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={handlePrevPage} aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </button>
                        </li>

                        {Array.from({length: totalPages}, (_, index) => index + 1).map((page) => (
                            <li key={page} className={`page-item ${currentPage === page ? 'active' : ''}`}>
                                <button className="page-link" onClick={() => handlePageClick(page)}>
                                    {page}
                                </button>
                            </li>
                        ))}

                        <li className={`page-item ${currentPage === totalPages ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={handleNextPage} aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

    );
}

export default Reports;