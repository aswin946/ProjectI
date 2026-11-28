/*
 * assets/js/charts.js
 * Draws the charts on the report pages using Chart.js (assets/js/chart.umd.js).
 *
 * The PHP page puts the data into the <canvas> tag as data-* attributes, for example:
 *   <canvas id="monthlyChart" data-labels='["Jan","Feb"]' data-income='[100,200]' ...>
 * This file reads that data and draws the chart. No data is typed in here.
 *
 * To change a chart later: edit the "type", "backgroundColor" or "options" below.
 */

// Reads a JSON list from a data-* attribute of an element
function readData(element, attributeName) {
    return JSON.parse(element.getAttribute(attributeName));
}

// ---------- Chart 1: monthly income vs expenses (bar chart) ----------
var monthlyCanvas = document.getElementById('monthlyChart');
if (monthlyCanvas && window.Chart) {
    new Chart(monthlyCanvas, {
        type: 'bar',
        data: {
            labels: readData(monthlyCanvas, 'data-labels'),
            datasets: [
                { label: 'Income',   data: readData(monthlyCanvas, 'data-income'),  backgroundColor: '#2f7d4f' },
                { label: 'Expenses', data: readData(monthlyCanvas, 'data-expense'), backgroundColor: '#b4543a' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// ---------- Chart 2: expense distribution by category (doughnut chart) ----------
var categoryCanvas = document.getElementById('categoryChart');
if (categoryCanvas && window.Chart) {
    new Chart(categoryCanvas, {
        type: 'doughnut',
        data: {
            labels: readData(categoryCanvas, 'data-labels'),
            datasets: [{
                data: readData(categoryCanvas, 'data-values'),
                backgroundColor: ['#0f766e', '#c98a0b', '#5b7c99', '#b4543a', '#7a9e7e',
                                  '#8a6fa8', '#d1704f', '#3e8e9c', '#a3a847', '#6b7780']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}
