class LocationService {
    constructor() {
        this.statusElement = document.getElementById('location-status');
        this.latitudeInput = document.getElementById('latitude');
        this.longitudeInput = document.getElementById('longitude');
    }

    async getLocation() {
        if (!navigator.geolocation) {
            this.updateStatus('Geolocation is not supported by your browser', 'error');
            return;
        }

        this.updateStatus('Fetching location...', 'info');

        try {
            const position = await this.getCurrentPosition();
            this.latitudeInput.value = position.coords.latitude;
            this.longitudeInput.value = position.coords.longitude;
            this.updateStatus('Location captured successfully!', 'success');
        } catch (error) {
            this.updateStatus('Unable to retrieve your location', 'error');
        }
    }

    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject);
        });
    }

    updateStatus(message, type) {
        if (this.statusElement) {
            this.statusElement.textContent = message;
            this.statusElement.className = `status-${type}`;
        }
    }
}

// Initialize location service when needed
document.addEventListener('DOMContentLoaded', () => {
    const locationButton = document.getElementById('get-location');
    if (locationButton) {
        const locationService = new LocationService();
        locationButton.addEventListener('click', () => locationService.getLocation());
    }
}); 