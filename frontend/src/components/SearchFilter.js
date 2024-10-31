import React, { useState, useEffect, useCallback } from 'react';
import debounce from 'lodash.debounce';

const SearchFilter = ({ onSearch }) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [fileType, setFileType] = useState('');
    const [fileSize, setFileSize] = useState('');

    // Debounced search function for partial name search
    const debouncedSearch = useCallback(
        debounce((filters) => {
            onSearch(filters);
        }, 300), // 300ms delay
        []
    );

    // Effect to trigger the debounced search when searchTerm changes
    useEffect(() => {
        if (searchTerm.length >= 2 || searchTerm.length === 0) {
            debouncedSearch({ name: searchTerm });
        }

        return () => {
            debouncedSearch.cancel();
        };
    }, [searchTerm, debouncedSearch]);

    const handleFileTypeChange = (e) => {
        setFileType(e.target.value);
    };

    const handleFileSizeChange = (e) => {
        setFileSize(e.target.value);
    };

    const handleSearchClick = () => {
        // Manual search including all filters
        onSearch({
            name: searchTerm,
            type: fileType,
            size: fileSize,
        });
    };

    return (
        <div className="row mb-4">
            <div className="col-md-4">
                <input
                    type="text"
                    className="form-control"
                    placeholder="Search by file name..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </div>
            <div className="col-md-3">
                <select
                    className="form-select"
                    value={fileType}
                    onChange={handleFileTypeChange}
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
                    onChange={handleFileSizeChange}
                >
                    <option value="">Filter by Size</option>
                    <option value="small">0 - 10 MB</option>
                    <option value="medium">10 - 100 MB</option>
                    <option value="large">100+ MB</option>
                </select>
            </div>
            <div className="col-md-2">
                <button className="btn btn-success w-100" onClick={handleSearchClick}>
                    Search
                </button>
            </div>
        </div>
    );
};

export default SearchFilter;
