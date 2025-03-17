/**
 * Mock Data Generator for PCDS2030 Dashboard
 * This file provides placeholder data for testing the UI without an operational backend.
 * Include this file before target_status.js to enable mock data mode.
 */

// Enable mock data mode
window.PCDS_MOCK_DATA = true;

// Mock user data
const currentUserMock = {
    id: 101,
    username: 'forestry_user',
    agencyId: 2,
    agencyName: 'Forestry Department',
    allowedMetricTypes: [
        { id: 'forestry', name: 'Forestry' },
        { id: 'conservation', name: 'Conservation' }
    ]
};

// Mock program data per agency
const programsMock = {
    1: [ // Main Agency
        {id: 'prog_101', name: 'Economic Policy Development'},
        {id: 'prog_102', name: 'Governance Framework'}
    ],
    2: [ // Forestry Department
        {id: 'prog_201', name: 'Reforestation Initiative'},
        {id: 'prog_202', name: 'Wildlife Conservation'},
        {id: 'prog_203', name: 'Sustainable Forestry Practices'}
    ],
    3: [ // Water Resources Department
        {id: 'prog_301', name: 'Watershed Management'},
        {id: 'prog_302', name: 'Water Quality Monitoring'}
    ],
    4: [ // Energy Department
        {id: 'prog_401', name: 'Renewable Energy Development'},
        {id: 'prog_402', name: 'Energy Efficiency Programs'}
    ],
    5: [ // Environmental Protection Agency
        {id: 'prog_501', name: 'Air Quality Monitoring'},
        {id: 'prog_502', name: 'Waste Management Initiative'}
    ]
};

// Mock agency data
const agenciesMock = [
    { AgencyID: 1, AgencyName: 'Main Agency' },
    { AgencyID: 2, AgencyName: 'Forestry Department' },
    { AgencyID: 3, AgencyName: 'Water Resources Department' },
    { AgencyID: 4, AgencyName: 'Energy Department' },
    { AgencyID: 5, AgencyName: 'Environmental Protection Agency' }
];

// Mock metric types
const metricTypesMock = [
    { id: 'forestry', name: 'Forestry' },
    { id: 'conservation', name: 'Conservation' },
    { id: 'land', name: 'Land Use' },
    { id: 'water', name: 'Water Resources' },
    { id: 'energy', name: 'Energy' },
    { id: 'social', name: 'Social Development' },
    { id: 'economic', name: 'Economic Development' },
    { id: 'governance', name: 'Governance' }
];

// Agency-specific allowed metric types
const agencyMetricTypesMock = {
    1: [{ id: 'governance', name: 'Governance' }, { id: 'economic', name: 'Economic Development' }],
    2: [{ id: 'forestry', name: 'Forestry' }, { id: 'conservation', name: 'Conservation' }],
    3: [{ id: 'water', name: 'Water Resources' }],
    4: [{ id: 'energy', name: 'Energy' }],
    5: [{ id: 'conservation', name: 'Conservation' }, { id: 'land', name: 'Land Use' }, { id: 'water', name: 'Water Resources' }]
};

// Mock submissions data - update with status colors
const submissionsMock = [
    {
        id: 's101',
        programName: 'Reforestation Initiative',
        year: '2024',
        quarter: 'Q2',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        targetValue: '500 ha',
        currentValue: '200 ha',
        lastUpdated: '2024-06-10',
        status: 'in-progress',
        statusColor: 'progress', // Yellow
        isEditable: true
    },
    {
        id: 's102',
        programName: 'Wildlife Conservation',
        year: '2024',
        quarter: 'Q2',
        metricType: 'conservation',
        metricTypeName: 'Conservation',
        agencyId: 2,
        agencyName: 'Forestry Department',
        targetValue: '50 species',
        currentValue: '42 species',
        lastUpdated: '2024-06-05',
        status: 'nearly-complete',
        statusColor: 'completed', // Green
        isEditable: true
    },
    {
        id: 's103',
        programName: 'Sustainable Forestry Practices',
        year: '2024',
        quarter: 'Q1',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        targetValue: '20 workshops',
        currentValue: '20 workshops',
        lastUpdated: '2024-03-15',
        status: 'completed',
        statusColor: 'completed', // Green
        isEditable: true
    },
    {
        id: 's104',
        programName: 'Land Reclamation Project',
        year: '2024',
        quarter: 'Q1',
        metricType: 'land',
        metricTypeName: 'Land Use',
        agencyId: 1,
        agencyName: 'Land Department',
        targetValue: '200 ha',
        currentValue: '180 ha',
        lastUpdated: '2024-03-28',
        status: 'nearly-complete',
        isEditable: false
    },
    {
        id: 's105',
        programName: 'Watershed Management',
        year: '2023',
        quarter: 'Q4',
        metricType: 'water',
        metricTypeName: 'Water Resources',
        agencyId: 3,
        agencyName: 'Water Resources Department',
        targetValue: '15 watersheds',
        currentValue: '12 watersheds',
        lastUpdated: '2023-12-20',
        status: 'in-progress',
        isEditable: false
    },
    {
        id: 's106',
        programName: 'Renewable Energy Development',
        year: '2024',
        quarter: 'Q2',
        metricType: 'energy',
        metricTypeName: 'Energy',
        agencyId: 4,
        agencyName: 'Energy Department',
        targetValue: '5 MW',
        currentValue: '2 MW',
        lastUpdated: '2024-05-15',
        status: 'in-progress',
        isEditable: false
    },
    {
        id: 's107',
        programName: 'Community Forestry Workshop',
        year: '2024',
        quarter: 'Q1',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        targetSummary: 'Organize 3 community workshops',
        currentValue: '',
        statusSummary: 'Successfully completed 2 workshops',
        lastUpdated: '2024-03-25',
        statusCategory: 'in-progress',
        statusColor: 'warning', // Red (severe delays)
        isQualitative: true,
        isEditable: true
    },
    {
        id: 's108',
        programName: 'Forest Policy Review',
        year: '2024',
        quarter: 'Q1',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        targetSummary: 'Complete policy document draft',
        currentValue: '',
        statusSummary: 'Draft completed and under review',
        lastUpdated: '2024-03-28',
        statusCategory: 'nearly-complete',
        isQualitative: true,
        isEditable: true
    }
];

// Mock submission details (expanded version of submissions)
const submissionDetailsMock = {
    's101': {
        id: 's101',
        programName: 'Reforestation Initiative',
        description: 'Program focusing on restoring forest cover in degraded areas across the country.',
        year: '2024',
        quarter: 'Q2',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        indicator: 'Reforested Area',
        targetValue: '500',
        targetUnit: 'ha',
        targetDeadline: '2024-12-31',
        currentValue: '200',
        completionPercentage: 40,
        statusDate: '2024-06-10',
        statusNotes: 'Planting is proceeding as scheduled in the northern regions. Challenges with weather delays in southern areas.',
        challenges: 'Unusual rainfall patterns have delayed planting in the southern regions. Working on adjusting the schedule.',
        lastUpdated: '2024-06-10',
        status: 'in-progress',
        submittedBy: 'forestry_user',
        isEditable: true,
        supportingFiles: [
            {name: 'progress_report.pdf', url: '#'},
            {name: 'site_photos.zip', url: '#'}
        ]
    },
    's102': {
        id: 's102',
        programName: 'Wildlife Conservation',
        description: 'Program aimed at protecting endangered species and their habitats.',
        year: '2024',
        quarter: 'Q2',
        metricType: 'conservation',
        metricTypeName: 'Conservation',
        agencyId: 2,
        agencyName: 'Forestry Department',
        indicator: 'Protected Species Count',
        targetValue: '50',
        targetUnit: 'species',
        targetDeadline: '2024-09-30',
        currentValue: '42',
        completionPercentage: 84,
        statusDate: '2024-06-05',
        statusNotes: 'Conservation efforts have been successful with 42 species now under protection programs.',
        challenges: 'Limited funding for the last 8 species that require specialized protection measures.',
        lastUpdated: '2024-06-05',
        status: 'nearly-complete',
        submittedBy: 'forestry_user',
        isEditable: true,
        supportingFiles: [
            {name: 'species_report.pdf', url: '#'}
        ]
    },
    's107': {
        id: 's107',
        programName: 'Community Forestry Workshop',
        description: 'Program to engage local communities in sustainable forestry practices through educational workshops.',
        year: '2024',
        quarter: 'Q1',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        indicator: 'Community Workshop Series',
        targetDescription: 'Organize 3 workshops in different regions to educate locals on sustainable forestry practices',
        targetType: 'qualitative',
        targetDeadline: '2024-03-31',
        statusDate: '2024-03-25',
        statusCategory: 'in-progress',
        statusNotes: 'Successfully completed 2 workshops with total attendance of 45 participants. One in North Region (22 attendees) and one in Central Region (23 attendees). The final workshop in South Region is scheduled for next week.',
        challenges: 'Transportation logistics to remote areas required additional planning and resources.',
        lastUpdated: '2024-03-25',
        isQualitative: true,
        submittedBy: 'forestry_user',
        isEditable: true,
        supportingFiles: [
            {name: 'workshop_agenda.pdf', url: '#'},
            {name: 'workshop_photos.zip', url: '#'},
            {name: 'attendance_sheet.xlsx', url: '#'}
        ]
    },
    's108': {
        id: 's108',
        programName: 'Forest Policy Review',
        description: 'Review and update of the national forestry policy document to align with sustainability goals.',
        year: '2024',
        quarter: 'Q1',
        metricType: 'forestry',
        metricTypeName: 'Forestry',
        agencyId: 2,
        agencyName: 'Forestry Department',
        indicator: 'Policy Document Update',
        targetDescription: 'Complete draft revision of the national forestry policy document incorporating latest sustainability research and stakeholder input',
        targetType: 'qualitative',
        targetDeadline: '2024-03-31',
        statusDate: '2024-03-28',
        statusCategory: 'nearly-complete',
        statusNotes: 'Draft policy document has been completed and is currently under review by the department heads. All sections have been updated with relevant research findings and stakeholder consultation outcomes. Final approval expected within the next week.',
        challenges: 'Reconciling competing priorities between conservation goals and economic development required extensive stakeholder negotiation.',
        lastUpdated: '2024-03-28',
        isQualitative: true,
        submittedBy: 'forestry_user',
        isEditable: true,
        supportingFiles: [
            {name: 'policy_draft_v3.pdf', url: '#'},
            {name: 'stakeholder_feedback.pdf', url: '#'}
        ]
    }
};

// Store submissions (for create/update operations)
let storedSubmissions = [...submissionsMock];
let lastSubmissionId = 's106'; // Track the last ID to create new ones

// Mock API functions:

// Get current user
window.fetchCurrentUser = function() {
    console.log('[MOCK] Fetching current user');
    return new Promise(resolve => {
        setTimeout(() => {
            resolve(currentUserMock);
        }, 300);
    });
};

// Get metric types
window.fetchMetricTypes = function() {
    console.log('[MOCK] Fetching all metric types');
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({
                success: true,
                data: metricTypesMock
            });
        }, 200);
    });
};

// Get agency metric types
window.fetchAgencyMetricTypes = function(agencyId = currentUserMock.agencyId) {
    console.log('[MOCK] Fetching agency metric types for agency', agencyId);
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({
                success: true,
                data: agencyMetricTypesMock[agencyId] || []
            });
        }, 200);
    });
};

// Get agencies
window.fetchAgencies = function() {
    console.log('[MOCK] Fetching agencies');
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({
                success: true,
                data: agenciesMock
            });
        }, 200);
    });
};

// Get programs for agency
window.fetchPrograms = function(agencyId = currentUserMock.agencyId) {
    console.log('[MOCK] Fetching programs for agency', agencyId);
    return new Promise(resolve => {
        setTimeout(() => {
            resolve({
                success: true,
                data: programsMock[agencyId] || []
            });
        }, 300);
    });
};

// Get submissions
window.fetchSubmissions = function(filters = {}) {
    console.log('[MOCK] Fetching submissions with filters:', filters);
    return new Promise(resolve => {
        setTimeout(() => {
            // Apply filters
            let filteredSubmissions = [...storedSubmissions];
            
            if (filters.year) {
                filteredSubmissions = filteredSubmissions.filter(s => s.year === filters.year);
            }
            
            if (filters.quarter) {
                filteredSubmissions = filteredSubmissions.filter(s => s.quarter === filters.quarter);
            }
            
            if (filters.metricType) {
                filteredSubmissions = filteredSubmissions.filter(s => s.metricType === filters.metricType);
            }
            
            if (filters.agencyId) {
                filteredSubmissions = filteredSubmissions.filter(s => s.agencyId === parseInt(filters.agencyId));
            }
            
            resolve({
                success: true,
                data: filteredSubmissions
            });
        }, 400);
    });
};

// Get submission details
window.fetchSubmissionDetails = function(submissionId) {
    console.log('[MOCK] Fetching submission details for', submissionId);
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            const details = submissionDetailsMock[submissionId];
            
            if (details) {
                resolve({
                    success: true,
                    data: details
                });
            } else {
                // For IDs we don't have detailed data for, create some basic details
                const submission = storedSubmissions.find(s => s.id === submissionId);
                if (submission) {
                    const basicDetails = {
                        id: submission.id,
                        programName: submission.programName,
                        description: `Description for ${submission.programName}`,
                        year: submission.year,
                        quarter: submission.quarter,
                        metricType: submission.metricType,
                        metricTypeName: submission.metricTypeName,
                        agencyId: submission.agencyId,
                        agencyName: submission.agencyName,
                        indicator: submission.programName.split(' ')[0] + ' Indicator',
                        targetValue: submission.targetValue.split(' ')[0],
                        targetUnit: submission.targetValue.split(' ')[1] || 'units',
                        targetDeadline: `${submission.year}-${submission.quarter === 'Q1' ? '03' : submission.quarter === 'Q2' ? '06' : submission.quarter === 'Q3' ? '09' : '12'}-30`,
                        currentValue: submission.currentValue.split(' ')[0],
                        completionPercentage: submission.status === 'completed' ? 100 : (submission.status === 'nearly-complete' ? 80 : 40),
                        statusDate: submission.lastUpdated,
                        statusNotes: `Status notes for ${submission.programName}`,
                        lastUpdated: submission.lastUpdated,
                        status: submission.status,
                        submittedBy: 'system_user',
                        isEditable: submission.isEditable,
                        supportingFiles: []
                    };
                    
                    resolve({
                        success: true,
                        data: basicDetails
                    });
                } else {
                    reject({
                        success: false,
                        message: 'Submission not found'
                    });
                }
            }
        }, 300);
    });
};

// Update the saveSubmission function to handle status color
window.saveSubmission = function(submissionData) {
    console.log('[MOCK] Saving submission data:', submissionData);
    return new Promise(resolve => {
        setTimeout(() => {
            // Create a new submission ID
            const numericPart = parseInt(lastSubmissionId.substring(1)) + 1;
            const newId = 's' + numericPart;
            lastSubmissionId = newId;
            
            // Create a simplified submission record
            const newSubmission = {
                id: newId,
                programName: submissionData.programName,
                year: submissionData.year,
                quarter: submissionData.quarter,
                metricType: submissionData.metricType,
                metricTypeName: metricTypesMock.find(m => m.id === submissionData.metricType)?.name || submissionData.metricType,
                agencyId: currentUserMock.agencyId,
                agencyName: currentUserMock.agencyName,
                targetValue: `${submissionData.targetValue} ${submissionData.targetUnit}`,
                currentValue: `${submissionData.currentValue} ${submissionData.targetUnit}`,
                lastUpdated: new Date().toISOString().split('T')[0],
                status: submissionData.isDraft ? 'draft' : submissionData.statusCategory,
                statusColor: submissionData.statusColor, // Add the status color
                isEditable: true
            };
            
            // Add to stored submissions
            storedSubmissions.unshift(newSubmission);
            
            resolve({
                success: true,
                message: submissionData.isDraft ? 'Draft saved successfully' : 'Data submitted successfully',
                metricId: newId
            });
        }, 800);
    });
};

console.log('Mock data is loaded and ready for testing');
