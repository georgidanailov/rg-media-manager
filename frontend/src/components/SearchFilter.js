import React from 'react';

const SearchFilter = () => {
    return (
        <div className="row mb-4">
            <div className="col-md-4">
                <input type="text" className="form-control" placeholder="Search by file name..." />
            </div>
            <div className="col-md-3">
                <select className="form-select">
                    <option value="">Filter by Type</option>
                    <option value="video">Video</option>
                    <option value="image">Image</option>
                    <option value="document">Document</option>
                </select>
            </div>
            <div className="col-md-3">
                <select className="form-select">
                    <option value="">Filter by Size</option>
                    <option value="small">0 - 10 MB</option>
                    <option value="medium">10 - 100 MB</option>
                    <option value="large">100+ MB</option>
                </select>
            </div>
            <div className="col-md-2">
                <button className="btn btn-success w-100">Search</button>
            </div>
        </div>
    );
};

export default SearchFilter;
