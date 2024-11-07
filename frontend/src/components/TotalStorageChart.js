import React from 'react';
import { Doughnut } from 'react-chartjs-2';

import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);
const TotalStorageChart = ({ storageData }) => {
    const data = {
        labels: storageData.map(item => item.name),
        datasets: [
            {
                label: 'Total Storage (MB)',
                data: storageData.map(item => item.totalStorage / 1024 / 1024),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ],
                hoverBackgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }
        ]
    };

    return (
        <div>
            <div className={"storageChartDiv"}>
                <h3>Total Storage Breakdown</h3>
                <div id={"totalStorageChart"}>
                    <Doughnut data={data}/>
                </div>
            </div>
        </div>
    );
};

export default TotalStorageChart;