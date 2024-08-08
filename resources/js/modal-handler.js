function openAddScheduleModal(clickedDate) {
    const modal = document.getElementById('scheduleModal');
    const startDateInput = document.getElementById('start');
    const endDateInput = document.getElementById('end');
    const dateToUse = clickedDate || new Date().toISOString().split('T')[0];

    startDateInput.value = dateToUse;
    endDateInput.value = dateToUse;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
}

function openScheduleDetailsModal(scheduleData) {
    const scheduleEndDateStr = scheduleData.end_date;
    const scheduleEndDate = new Date(scheduleEndDateStr);
    scheduleEndDate.setDate(scheduleEndDate.getDate() - 1);
    const formattedEndDate = scheduleEndDate.toISOString().split('T')[0];
    
    // Set schedule details in the modal
    document.getElementById('singleScheduleTitle').textContent = `Title: ${scheduleData.title}`;
    document.getElementById('scheduleStartDate').textContent = `Start Date: ${scheduleData.start_date}`;
    document.getElementById('scheduleEndDate').textContent = `End Date: ${formattedEndDate}`;
    document.getElementById('scheduleDescription').textContent =
        `${scheduleData.description ? 'Description: ' : ''} ${scheduleData.description || ""}`;

    // Open the modal
    const modal = document.getElementById('scheduleDetailModal');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100', 'pointer-events-auto');
    document.getElementById('scheduleDetailModalContent').style.backgroundColor = scheduleData.color || '#ffffff'; // Default color if not provided
}
