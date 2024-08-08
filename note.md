dateClick: function(info) {
                var clickedDate = info.dateStr;
                alert(clickedDate)
                $.ajax({
                    url: '/schedule/check',
                    method: 'GET',
                    data: {
                        date: clickedDate
                    },
                    success: function(response) {
                        if (response.hasEvent) {
                            // Display the event (you can customize this as needed)
                            alert('Event: ' + response.event.title);
                        } else {
                            openAddEventModal(dateStr);
                        }
                    },
                    error: function(error) {
                        console.error('Error checking date:', error);
                    }
                });