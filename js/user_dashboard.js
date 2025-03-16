document.addEventListener('DOMContentLoaded', () => {
    fetchSubmissions();
    fetchNotifications();
});

function fetchSubmissions() {
    // Placeholder for fetching submissions (replace with real API call)
    const submissions = [
        { user: 'User 1', type: 'TEV', month: 'January', year: 2024, value: 1000 },
        { user: 'User 2', type: 'Programme', name: 'Programme A', target: 'Target A', status: 'Completed', quarter: 1, year: 2024 },
        // Add more submissions as needed
    ];

    const submissionsList = document.getElementById('submissionsList');
    submissions.forEach(submission => {
        const listItem = document.createElement('div');
        listItem.className = 'list-group-item';
        listItem.innerHTML = `
            <h5 class="mb-1">${submission.type} Submission by ${submission.user}</h5>
            <p class="mb-1">${submission.type === 'TEV' ? `Month: ${submission.month}, Year: ${submission.year}, Value: RM${submission.value}` : `Programme: ${submission.name}, Target: ${submission.target}, Status: ${submission.status}, Quarter: Q${submission.quarter}, Year: ${submission.year}`}</p>
        `;
        submissionsList.appendChild(listItem);
    });
}

function fetchNotifications() {
    // Placeholder for fetching notifications (replace with real API call)
    const notifications = [
        { message: 'New TEV submission by User 1', date: '2024-01-15' },
        { message: 'New Programme submission by User 2', date: '2024-01-16' },
        // Add more notifications as needed
    ];

    const notificationsList = document.getElementById('notificationsList');
    notifications.forEach(notification => {
        const listItem = document.createElement('div');
        listItem.className = 'list-group-item';
        listItem.innerHTML = `
            <p class="mb-1">${notification.message}</p>
            <small>${notification.date}</small>
        `;
        notificationsList.appendChild(listItem);
    });
}
