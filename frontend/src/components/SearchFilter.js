import React, { useState, useEffect, useCallback } from 'react';
import debounce from 'lodash.debounce';
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
const SearchFilter = ({ onSearch }) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [fileType, setFileType] = useState('');
    const [fileSize, setFileSize] = useState('');
    const [users, setUsers] = useState([]);
    const [selectedUser, setSelectedUser] = useState('');
    const [tags, setTags] = useState([]);
    const [selectedTag, setSelectedTag] = useState([]);
    const [selectedDate, setSelectedDate] = useState('')


    useEffect(() => {
        axios.get('http://localhost:9000/users', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`
            }
        })
            .then(response => {
                setUsers(response.data);
            })
            .catch(error => {
                console.error('Error fetching users:', error);
            });
    }, []);

    useEffect(() => {
        axios.get('http://localhost:9000/tags', {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`
            }
        })
            .then(response => {
                setTags(response.data);
            })
            .catch(error => {
                console.error('Error fetching tags:', error);
            });
    }, []);

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
                user: selectedUser,
                tag: selectedTag,
                date: selectedDate
            });
        }

        // Cleanup function to cancel debounce if component is unmounted or searchTerm changes
        return () => {
            debouncedSearch.cancel();
        };
    }, [searchTerm, fileType, fileSize, selectedUser, selectedTag, selectedDate]);

    const handleFileTypeChange = (e) => {
        setFileType(e.target.value);
    };

    const handleFileSizeChange = (e) => {
        setFileSize(e.target.value);
    };

    const handleDateChange = (e) => {
        setSelectedDate(e.target.value);
    };

    const handleUserChange = (e) => {
        setSelectedUser(e.target.value);
    };

    const handleTagChange = (e) => {
        setSelectedTag(e.target.value);
    };

    return (
        <div className="row mb-4">
            <div className="col-md-2 mt-1">
                <input
                    type="text"
                    className="form-control"
                    placeholder="Search by file name..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)} // Update search term as the user types
                />
            </div>
            <div className="col-md-2 mt-1">
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
            <div className="col-md-2 mt-1">
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
            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                <div className="col-md-2 mt-1">
                    <select className="form-select" value={selectedDate} onChange={handleDateChange}>
                        <option value="">Filter by Upload Date</option>
                        <option value="24hours">Last 24 Hours</option>
                        <option value="lastWeek">Last 7 Days</option>
                        <option value="lastMonth">Last Month</option>
                        <option value="lastThreeMonths">Last 3 Months</option>
                        <option value="lastSixMonths">Last 6 Months</option>
                        <option value="lastYear">Last Year</option>
                    </select>
                </div>
            ) : null}
            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
                <div className="col-md-2 mt-1">
                    <select className="form-select" value={selectedUser} onChange={handleUserChange}>
                        <option value="">Filter by User</option>
                        {users.map(user =>
                            (<option key={user.username} value={user.id}>
                                {user.username}
                            </option>)
                        )}
                    </select>
                </div>

            ) : null}
            {role === "ROLE_ADMIN" || role === "ROLE_MODERATOR" ? (
            <div className="col-md-2 mt-1">
                <select className="form-select" value={selectedTag} onChange={handleTagChange}>
                    <option value="">Filter by Tags</option>
                    {tags.map(tag =>
                        (<option key={tag.name} value={tag.id}>
                            {tag.name}
                        </option>)
                    )}
                </select>
            </div>
            ) : null}
            <div className="col-md-2">
                <button className="btn btn-success w-100 mt-1"
                        onClick={() => onSearch({name: searchTerm, type: fileType, size: fileSize})}>
                    Search
                </button>
            </div>
        </div>
    );

};

export default SearchFilter;
