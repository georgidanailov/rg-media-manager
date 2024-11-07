import React from 'react';
import { Bar } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const FileTypeChart = ({ fileData}) => {
    const data = {
        labels: fileData.map(item=>item.name),
        datasets: [
            {
                label: "Images",
                data: fileData.map(item => item.images),
                backgroundColor: '#FF6384'
            },
            {
                label: "Videos",
                data: fileData.map(item => item.videos),
                backgroundColor: '#36A2EB'
            },
            {
                label: "Documents",
                data: fileData.map(item => item.documents),
                backgroundColor: '#FFCE56'
            },
            {
                label: "Archives",
                data: fileData.map(item => item.archives),
                backgroundColor: '#4BC0C0'
            }
        ]
    };

    const options = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            },
        },
        scales: {
            x: {
                stacked: true,
            },
            y: {
                stacked: true,
                title: {
                    display: true,
                    text: 'Number of Files',
                }
            }
        }
    };

    return (
        <div>
            <div className={"fileChartDiv"}>
                <h3>File Types Uploaded per User</h3>
                <div id={"fileTypeChart"}>
                    <Bar data={data} options={options} />
                </div>
            </div>
        </div>
    );

};

export default FileTypeChart;