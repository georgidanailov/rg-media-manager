import React, { useState, useEffect } from 'react';


const ActivityLogTable = ({logData}) => {
    if (!logData.length) {
        return <p>No Log data available</p>;
    }

    return (
        <div className={'table-responsive'}>
            <table className={'table table-striped table-bordered'}>
                <thead>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Timestamp</th>
                    <th>Type</th>
                </tr>
                </thead>
                <tbody>
                {logData.map((log, index) => (
                    <tr key={index}>
                        <td>{log.data.user_id}</td>
                        <td>{log.data.email}</td>
                        <td>{log.data.ip_address}</td>
                        <td>{log.data.user_agent}</td>
                        <td>{log.data.timestamp}</td>
                        <td>{log.type}</td>
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
};

export default ActivityLogTable;