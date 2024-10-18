import React from 'react';

const FileTable = () => {
    const files = [
        { id: 1, name: 'Robot_v2.mp4', uploadedDate: '06/03/23', size: '237 KB', type: 'Video' },
        { id: 2, name: 'Business_woman_filming.mov', uploadedDate: '03/03/23', size: '134 KB', type: 'Video' },
        { id: 3, name: 'New_promo_video.mov', uploadedDate: '01/03/23', size: '217 MB', type: 'Video' },
        { id: 4, name: 'Emp_comms_March_2023.mp4', uploadedDate: '25/02/23', size: '345 MB', type: 'Video' },
        { id: 5, name: 'test.mkv', uploadedDate: '22/02/23', size: '237 KB', type: 'Video' },
    ];

    return (
        <div className="table-responsive">
            <table className="table table-striped table-hover table-bordered">
                <thead className="table-light">
                <tr>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded Date</th>
                    <th>Size</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                {files.map((file) => (
                    <tr key={file.id}>
                        <td>{file.name}</td>
                        <td>{file.type}</td>
                        <td>{file.uploadedDate}</td>
                        <td>{file.size}</td>
                        <td>
                            <button className="btn btn-success btn-sm me-2">Edit</button>
                            <button className="btn btn-success btn-sm me-2">Delete</button>
                            <button className="btn btn-success btn-sm">Download</button>
                        </td>
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
};

export default FileTable;
