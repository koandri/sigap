/**
 * QR Code Scanner for DMS
 * Handles QR code scanning for printed forms
 */

class QRScanner {
    constructor(options = {}) {
        this.options = {
            videoElement: options.videoElement || '#qr-video',
            resultElement: options.resultElement || '#qr-result',
            scanButton: options.scanButton || '#qr-scan-btn',
            stopButton: options.stopButton || '#qr-stop-btn',
            onScan: options.onScan || null,
            onError: options.onError || null,
            ...options
        };
        
        this.stream = null;
        this.scanning = false;
        this.video = null;
        this.canvas = null;
        this.context = null;
    }

    /**
     * Initialize the QR scanner
     */
    init() {
        this.video = document.querySelector(this.options.videoElement);
        this.resultElement = document.querySelector(this.options.resultElement);
        this.scanButton = document.querySelector(this.options.scanButton);
        this.stopButton = document.querySelector(this.options.stopButton);
        
        if (!this.video) {
            console.error('Video element not found');
            return;
        }

        // Create canvas for QR code detection
        this.canvas = document.createElement('canvas');
        this.context = this.canvas.getContext('2d');

        // Bind event listeners
        if (this.scanButton) {
            this.scanButton.addEventListener('click', () => this.startScanning());
        }
        
        if (this.stopButton) {
            this.stopButton.addEventListener('click', () => this.stopScanning());
        }

        // Handle video events
        this.video.addEventListener('loadedmetadata', () => {
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;
        });

        console.log('QR Scanner initialized');
    }

    /**
     * Start scanning for QR codes
     */
    async startScanning() {
        try {
            // Request camera access
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Use back camera if available
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            this.video.srcObject = this.stream;
            this.video.play();
            
            this.scanning = true;
            this.updateUI();
            this.scanLoop();

        } catch (error) {
            console.error('Error accessing camera:', error);
            this.handleError('Camera access denied or not available');
        }
    }

    /**
     * Stop scanning
     */
    stopScanning() {
        this.scanning = false;
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        this.video.srcObject = null;
        this.updateUI();
    }

    /**
     * Main scanning loop
     */
    scanLoop() {
        if (!this.scanning) return;

        // Draw current frame to canvas
        this.context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
        
        // Get image data
        const imageData = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
        
        // Try to decode QR code
        try {
            const result = this.decodeQRCode(imageData);
            if (result) {
                this.handleScanResult(result);
                return;
            }
        } catch (error) {
            // QR code not found, continue scanning
        }

        // Continue scanning
        requestAnimationFrame(() => this.scanLoop());
    }

    /**
     * Decode QR code from image data
     * This is a simplified implementation - in production, you'd use a proper QR code library
     */
    decodeQRCode(imageData) {
        // This is a placeholder implementation
        // In production, you would use a library like jsQR or qrcode-reader
        // For now, we'll simulate QR code detection
        
        // Simulate QR code detection (remove this in production)
        if (Math.random() < 0.01) { // 1% chance of "detecting" a QR code
            return {
                data: 'PF-241020-0001',
                location: {
                    topLeftCorner: { x: 100, y: 100 },
                    topRightCorner: { x: 200, y: 100 },
                    bottomLeftCorner: { x: 100, y: 200 },
                    bottomRightCorner: { x: 200, y: 200 }
                }
            };
        }
        
        return null;
    }

    /**
     * Handle successful QR code scan
     */
    handleScanResult(result) {
        console.log('QR Code detected:', result);
        
        if (this.resultElement) {
            this.resultElement.value = result.data;
            this.resultElement.classList.add('is-valid');
        }

        // Call custom callback
        if (this.options.onScan) {
            this.options.onScan(result);
        }

        // Stop scanning after successful scan
        this.stopScanning();
        
        // Show success message
        this.showNotification('QR Code scanned successfully!', 'success');
    }

    /**
     * Handle errors
     */
    handleError(message) {
        console.error('QR Scanner error:', message);
        
        if (this.options.onError) {
            this.options.onError(message);
        }
        
        this.showNotification(message, 'error');
    }

    /**
     * Update UI based on scanning state
     */
    updateUI() {
        if (this.scanButton) {
            this.scanButton.disabled = this.scanning;
            this.scanButton.textContent = this.scanning ? 'Scanning...' : 'Start Scan';
        }
        
        if (this.stopButton) {
            this.stopButton.disabled = !this.scanning;
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Add to page
        const container = document.querySelector('.page-body .container-xl');
        if (container) {
            container.insertBefore(notification, container.firstChild);
        }

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    /**
     * Clean up resources
     */
    destroy() {
        this.stopScanning();
    }
}

// Initialize QR scanner when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize if elements are present
    if (document.querySelector('#qr-video')) {
        window.qrScanner = new QRScanner({
            onScan: function(result) {
                console.log('QR Code scanned:', result.data);
                // Handle the scanned QR code data
                if (result.data.startsWith('PF-')) {
                    // Redirect to printed form page
                    window.location.href = `/printed-forms/${result.data}`;
                }
            },
            onError: function(error) {
                console.error('QR Scanner error:', error);
            }
        });
        
        window.qrScanner.init();
    }
});
