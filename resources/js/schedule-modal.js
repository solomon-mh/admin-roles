        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('scheduleModal');
            const openModalButton = document.getElementById(
                'openModal'); // You will need a button with this ID to open the modal
            const closeModalButton = document.getElementById('closeModal');

            if (openModalButton) {
                openModalButton.addEventListener('click', function() {
                    modal.classList.remove('opacity-0', 'pointer-events-none');
                    modal.classList.add('opacity-100', 'pointer-events-auto');
                });
            }

            if (closeModalButton) {
                closeModalButton.addEventListener('click', function() {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modal.classList.remove('opacity-100', 'pointer-events-auto');
                });
            }

            // Optional: Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modal.classList.remove('opacity-100', 'pointer-events-auto');
                }
            });
        });
