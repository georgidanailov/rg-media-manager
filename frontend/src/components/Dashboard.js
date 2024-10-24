import React from 'react';
import FileTable from './FileTable';
import SearchFilter from './SearchFilter';
import Pagination from './Pagination';
import FileUpload from "./FileUpload";

const Dashboard = () => {
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
                        <button className="btn btn-success btn-lg">Upload</button>
                    </div>
                    <SearchFilter />
                    <FileTable />
                    <FileUpload />
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
