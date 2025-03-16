document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileNavToggle = document.createElement('button');
    mobileNavToggle.classList.add('mobile-nav-toggle');
    mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('header').appendChild(mobileNavToggle);
    
    const headerRight = document.querySelector('.header-right');
    
    mobileNavToggle.addEventListener('click', function() {
        headerRight.classList.toggle('active');
        this.innerHTML = headerRight.classList.contains('active') ? 
            '<i class="fas fa-times"></i>' : 
            '<i class="fas fa-bars"></i>';
    });
    
    // Make tables mobile-friendly by adding data attributes
    const tables = document.querySelectorAll('.data-table');
    tables.forEach(table => {
        const headerCells = table.querySelectorAll('thead th');
        const headerLabels = Array.from(headerCells).map(cell => cell.textContent.trim());
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const dataCells = row.querySelectorAll('td');
            dataCells.forEach((cell, index) => {
                if (index < headerLabels.length) {
                    cell.setAttribute('data-label', headerLabels[index]);
                }
            });
        });
    });
    
    // Add viewport check for orientation changes
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && headerRight.classList.contains('active')) {
            headerRight.classList.remove('active');
            mobileNavToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
    
    // Form optimization for mobile
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
        const inputs = group.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            // Add better focus styles for mobile
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused-mobile');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused-mobile');
            });
        });
    });
});
