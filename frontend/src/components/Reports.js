import React, {useEffect, useState} from "react";
import TotalStorageChart from "./TotalStorageChart";
import axios from 'axios';
import '../Charts.css';
import FileTypesChart from "./FileTypesChart";

const Reports = () => {

    const [storageData, setStorageData] = useState([]);
    const [fileTypeData, setFileTypeData] = useState([]);

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
        </div>

    );
}

export default Reports;