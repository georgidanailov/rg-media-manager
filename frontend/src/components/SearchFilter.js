import React, { useState, useEffect, useCallback } from 'react';
import debounce from 'lodash.debounce';

const SearchFilter = ({ onSearch }) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [fileType, setFileType] = useState('');
    const [fileSize, setFileSize] = useState('');

    // Debounced search function
    const debouncedSearch = useCallback(
        debounce((filters) => {
            onSearch(filters);
        }, 300), // 300ms delay
        []
    );

    // Effect to trigger the debounced search when searchTerm changes
    useEffect(() => {
        // Only trigger search if searchTerm is at least 3 characters
        if (searchTerm.length >= 3 || searchTerm.length === 0) {
            debouncedSearch({
                name: searchTerm,
                type: fileType,
                size: fileSize,
            });
        }

        // Cleanup function to cancel debounce if component is unmounted or searchTerm changes
        return () => {
            debouncedSearch.cancel();
        };
    }, [searchTerm, fileType, fileSize, debouncedSearch]);

    const handleFileTypeChange = (e) => {
        setFileType(e.target.value);
    };

    const handleFileSizeChange = (e) => {
        setFileSize(e.target.value);
    };

    return (
        <div className="row mb-4">
            <div className="col-md-4">
                <input
                    type="text"
                    className="form-control"
                    placeholder="Search by file name..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)} // Update search term as the user types
                />
            </div>
            <div className="col-md-3">
                <select
                    className="form-select"
                    value={fileType}
                    onChange={handleFileTypeChange} // Update file type
                >
                    <option value="">Filter by Type</option>
                    <option value="video">Video</option>
                    <option value="image">Image</option>
                    <option value="document">Document</option>
                    <option value="archive">Archive</option>
                </select>
            </div>
            <div className="col-md-3">
                <select
                    className="form-select"
                    value={fileSize}
                    onChange={handleFileSizeChange} // Update file size
                >
                    <option value="">Filter by Size</option>
                    <option value="small">0 - 10 MB</option>
                    <option value="medium">10 - 100 MB</option>
                    <option value="large">100+ MB</option>
                </select>
            </div>
            <div className="col-md-2">
                <button className="btn btn-success w-100" onClick={() => onSearch({ name: searchTerm, type: fileType, size: fileSize })}>
                    Search
                </button>
            </div>
        </div>
    );
};

export default SearchFilter;
