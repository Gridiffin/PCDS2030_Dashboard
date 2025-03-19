/**
 * Main entry point for the Manage Metrics feature
 * This file imports and initializes the modular components
 */
import { loadCurrentUser, initTabSystem } from './modules/metrics/metrics_core.js';
import initMetricsDefinitionTab from './modules/metrics/metrics_definition.js';
import initMetricsReportingTab from './modules/metrics/metrics_reporting.js';

document.addEventListener('DOMContentLoaded', async function() {
    console.log("DOM Content Loaded - Initializing manage_metrics.js");
    
    // Initialize the tab system
    const switchTab = initTabSystem();
    console.log("Tab system initialized");
    
    // Load current user data
    const userLoaded = await loadCurrentUser();
    console.log("User data loaded:", userLoaded);
    
    // Initialize the definition tab
    const metricsDefinition = initMetricsDefinitionTab();
    metricsDefinition.init();
    console.log("Metrics definition tab initialized");
    
    // Initialize the reporting tab
    const metricsReporting = initMetricsReportingTab();
    metricsReporting.init();
    console.log("Metrics reporting tab initialized");
    
    // Event listener for tab changes that need special handling
    document.addEventListener('tabactivated', (e) => {
        console.log(`Tab activated: ${e.detail.tabId}`);
        // If moving to report tab with a metric already selected via definition tab
        if (e.detail.tabId === 'report' && sessionStorage.getItem('selectedMetricId')) {
            console.log('Direct metric reporting selected');
            // The metrics_reporting module will handle the selected metric internally
        }
    });
    
    // Fix the modal close button and prevent default behavior
    const modal = document.getElementById('detailModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const modalButton = document.querySelector('.modal-footer .modal-button');
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default behavior
            e.stopPropagation(); // Prevent event bubbling
            modal.classList.remove('active');
        });
    }
    
    if (modalButton) {
        modalButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default behavior
            modal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside content
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    
    // Force initial tab to be active based on URL hash or default to first tab
    let activeTabId = 'define';
    if (window.location.hash) {
        const tabId = window.location.hash.substring(1);
        if (document.querySelector(`.tab[data-tab="${tabId}"]`)) {
            activeTabId = tabId;
        }
    }
    console.log("Setting initial active tab to:", activeTabId);
    switchTab(activeTabId);
    
    // Handle tab navigation without page refresh
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any default action
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
            
            // Update URL hash without page reload
            window.history.replaceState(null, null, '#' + tabId);
        });
    });
    
    // Add ripple effect to all buttons with improved positioning
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', createRipple);
    });
    
    // Improved ripple effect function
    function createRipple(e) {
        const button = this;
        
        // Remove any existing ripple
        const existingRipple = button.querySelector('.ripple');
        if (existingRipple) {
            existingRipple.remove();
        }
        
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        
        // Get the button's position relative to the viewport
        const rect = button.getBoundingClientRect();
        
        // Calculate the position of the ripple
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${e.clientX - rect.left - radius}px`;
        circle.style.top = `${e.clientY - rect.top - radius}px`;
        
        circle.classList.add('ripple');
        button.appendChild(circle);
        
        // Remove the ripple after animation completes
        setTimeout(() => {
            if (circle.parentElement === button) {
                button.removeChild(circle);
            }
        }, 600);
    }
});
