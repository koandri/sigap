/**
 * OnlyOffice Document Editor Integration
 * Handles the OnlyOffice Document Editor initialization and callbacks
 */

class OnlyOfficeEditor {
    constructor(config) {
        this.config = config;
        this.editor = null;
        this.isInitialized = false;
    }

    /**
     * Initialize the OnlyOffice editor
     */
    init() {
        if (this.isInitialized) {
            return;
        }

        try {
            // Check if OnlyOffice API is available
            if (typeof DocsAPI === 'undefined') {
                console.error('OnlyOffice API not loaded');
                this.showError('OnlyOffice API not loaded. Please refresh the page.');
                return;
            }

            // Initialize the editor
            this.editor = new DocsAPI.DocEditor('onlyoffice-editor', this.config);
            
            this.isInitialized = true;
            
        } catch (error) {
            console.error('Failed to initialize OnlyOffice editor:', error);
            this.showError('Failed to initialize document editor. Please try again.');
        }
    }

    /**
     * Handle editor events
     */
    onDocumentStateChange(event) {
        // Handle different document states
        switch (event.data) {
            case 0: // Document not found
                this.showError('Document not found. Please check the document URL.');
                break;
            case 1: // Document is being edited
                this.showInfo('Document is being edited...');
                break;
            case 2: // Document is ready for saving
                this.showInfo('Document is ready for saving...');
                break;
            case 3: // Document saving error
                this.showError('Error saving document. Please try again.');
                break;
            case 4: // Document closed with no changes
                this.showInfo('Document closed with no changes.');
                break;
            case 6: // Document is being edited, but current state is saved
                this.showInfo('Document state saved.');
                break;
            case 7: // Error occurred while force saving
                this.showError('Error occurred while saving. Please try again.');
                break;
        }
    }

    /**
     * Handle document ready event
     */
    onDocumentReady() {
        this.hideLoading();
    }

    /**
     * Handle document save event
     */
    onDocumentSave(event) {
        // Send save callback to server
        this.sendSaveCallback(event);
    }

    /**
     * Send save callback to server
     */
    sendSaveCallback(event) {
        const callbackUrl = this.config.editorConfig.callbackUrl;
        
        if (!callbackUrl) {
            console.warn('No callback URL configured');
            return;
        }

        fetch(callbackUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(event)
        })
        .then(response => response.json())
        .then(data => {
            // Callback processed successfully
        })
        .catch(error => {
            console.error('Error sending save callback:', error);
        });
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        const loadingElement = document.getElementById('onlyoffice-loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        const loadingElement = document.getElementById('onlyoffice-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Show info message
     */
    showInfo(message) {
        this.showNotification(message, 'info');
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : 'info'} alert-dismissible`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Add to page
        const container = document.querySelector('.page-body .container-xl');
        if (container) {
            container.insertBefore(notification, container.firstChild);
        }

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    /**
     * Destroy the editor
     */
    destroy() {
        if (this.editor) {
            this.editor.destroyEditor();
            this.editor = null;
            this.isInitialized = false;
        }
    }
}

// Global functions for OnlyOffice callbacks
window.onDocumentStateChange = function(event) {
    if (window.onlyOfficeEditor) {
        window.onlyOfficeEditor.onDocumentStateChange(event);
    }
};

window.onDocumentReady = function() {
    if (window.onlyOfficeEditor) {
        window.onlyOfficeEditor.onDocumentReady();
    }
};

window.onDocumentSave = function(event) {
    if (window.onlyOfficeEditor) {
        window.onlyOfficeEditor.onDocumentSave(event);
    }
};

// Initialize editor when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const editorConfig = window.onlyOfficeConfig;
    if (editorConfig) {
        window.onlyOfficeEditor = new OnlyOfficeEditor(editorConfig);
        window.onlyOfficeEditor.init();
    }
});
