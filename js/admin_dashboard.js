document.addEventListener('DOMContentLoaded', () => {
    const reportForm = document.getElementById('reportForm');

    reportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const reportType = document.getElementById('reportType').value;
        const quarter = document.getElementById('quarter').value;
        const year = document.getElementById('year').value;

        if (reportType === 'ppt') {
            generatePPT(quarter, year);
        } else if (reportType === 'pdf') {
            generatePDF(quarter, year);
        }
    });
});

function generatePPT(quarter, year) {
    // Placeholder function to generate PPT
    alert(`Generating PPT for Q${quarter} ${year}`);
}

function generatePDF(quarter, year) {
    // Placeholder function to generate PDF
    alert(`Generating PDF for Q${quarter} ${year}`);
}
