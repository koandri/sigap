/**
 * DMS Forms JavaScript
 * Handles form interactions, SLA countdowns, and status updates
 */

class DMSForms {
    constructor() {
        this.slaTimers = new Map();
        this.statusUpdates = new Map();
        this.init();
    }

    /**
     * Initialize DMS Forms functionality
     */
    init() {
        this.initSLAcountdowns();
        this.initStatusTimeline();
        this.initFormQuantitySelectors();
        this.initBulkOperations();
        this.initStatusUpdates();
    }

    /**
     * Initialize SLA countdown timers
     */
    initSLAcountdowns() {
        const slaElements = document.querySelectorAll('[data-sla-countdown]');
        
        slaElements.forEach(element => {
            const targetDate = new Date(element.dataset.slaCountdown);
            const requestId = element.dataset.requestId;
            
            if (requestId) {
                this.slaTimers.set(requestId, {
                    element: element,
                    targetDate: targetDate,
                    interval: null
                });
                
                this.startSLAcountdown(requestId);
            }
        });
    }

    /**
     * Start SLA countdown for a specific request
     */
    startSLAcountdown(requestId) {
        const timer = this.slaTimers.get(requestId);
        if (!timer) return;

        const updateCountdown = () => {
            const now = new Date();
            const diff = timer.targetDate - now;
            
            if (diff <= 0) {
                // SLA exceeded
                timer.element.innerHTML = '<span class="text-danger">SLA Exceeded</span>';
                timer.element.classList.add('text-danger');
                clearInterval(timer.interval);
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            timer.element.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
            
            // Change color based on remaining time
            if (hours < 1) {
                timer.element.classList.add('text-warning');
            }
            if (hours < 0.5) {
                timer.element.classList.remove('text-warning');
                timer.element.classList.add('text-danger');
            }
        };

        updateCountdown();
        timer.interval = setInterval(updateCountdown, 1000);
    }

    /**
     * Initialize status timeline visualization
     */
    initStatusTimeline() {
        const timelineElements = document.querySelectorAll('.status-timeline');
        
        timelineElements.forEach(timeline => {
            const status = timeline.dataset.status;
            const steps = timeline.querySelectorAll('.timeline-step');
            
            steps.forEach((step, index) => {
                const stepStatus = step.dataset.status;
                
                if (stepStatus === 'completed') {
                    step.classList.add('completed');
                } else if (stepStatus === 'current') {
                    step.classList.add('current');
                } else {
                    step.classList.add('pending');
                }
            });
        });
    }

    /**
     * Initialize form quantity selectors
     */
    initFormQuantitySelectors() {
        const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
        
        quantityInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const quantity = parseInt(e.target.value);
                const formCard = e.target.closest('.form-card');
                
                if (quantity > 0) {
                    formCard.classList.add('selected');
                    formCard.querySelector('input[name*="[document_version_id]"]').disabled = false;
                } else {
                    formCard.classList.remove('selected');
                    formCard.querySelector('input[name*="[document_version_id]"]').disabled = true;
                }
                
                this.updateTotalQuantity();
            });
        });
    }

    /**
     * Update total quantity display
     */
    updateTotalQuantity() {
        const quantityInputs = document.querySelectorAll('input[name*="[quantity]"]');
        let totalQuantity = 0;
        let selectedForms = 0;
        
        quantityInputs.forEach(input => {
            const quantity = parseInt(input.value) || 0;
            if (quantity > 0) {
                totalQuantity += quantity;
                selectedForms++;
            }
        });
        
        const totalElement = document.getElementById('total-quantity');
        if (totalElement) {
            totalElement.textContent = totalQuantity;
        }
        
        const formsElement = document.getElementById('selected-forms');
        if (formsElement) {
            formsElement.textContent = selectedForms;
        }
    }

    /**
     * Initialize bulk operations
     */
    initBulkOperations() {
        const bulkCheckboxes = document.querySelectorAll('input[type="checkbox"][data-bulk-select]');
        const bulkActions = document.querySelectorAll('[data-bulk-action]');
        
        // Handle individual checkboxes
        bulkCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateBulkActions();
            });
        });
        
        // Handle select all checkbox
        const selectAllCheckbox = document.querySelector('#select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                bulkCheckboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                this.updateBulkActions();
            });
        }
        
        // Handle bulk actions
        bulkActions.forEach(action => {
            action.addEventListener('click', (e) => {
                e.preventDefault();
                const actionType = action.dataset.bulkAction;
                const selectedItems = this.getSelectedItems();
                
                if (selectedItems.length === 0) {
                    this.showNotification('Please select items to perform bulk action', 'warning');
                    return;
                }
                
                this.performBulkAction(actionType, selectedItems);
            });
        });
    }

    /**
     * Update bulk actions based on selection
     */
    updateBulkActions() {
        const selectedItems = this.getSelectedItems();
        const bulkActions = document.querySelectorAll('[data-bulk-action]');
        
        bulkActions.forEach(action => {
            action.disabled = selectedItems.length === 0;
        });
        
        const selectAllCheckbox = document.querySelector('#select-all');
        if (selectAllCheckbox) {
            const totalCheckboxes = document.querySelectorAll('input[type="checkbox"][data-bulk-select]').length;
            selectAllCheckbox.checked = selectedItems.length === totalCheckboxes;
            selectAllCheckbox.indeterminate = selectedItems.length > 0 && selectedItems.length < totalCheckboxes;
        }
    }

    /**
     * Get selected items
     */
    getSelectedItems() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][data-bulk-select]:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }

    /**
     * Perform bulk action
     */
    performBulkAction(actionType, selectedItems) {
        const actionText = this.getBulkActionText(actionType);
        
        if (confirm(`Are you sure you want to ${actionText.toLowerCase()} ${selectedItems.length} item(s)?`)) {
            this.sendBulkAction(actionType, selectedItems);
        }
    }

    /**
     * Get bulk action text
     */
    getBulkActionText(actionType) {
        const actionTexts = {
            'acknowledge': 'Acknowledge',
            'process': 'Process',
            'ready': 'Mark as Ready',
            'collect': 'Mark as Collected'
        };
        
        return actionTexts[actionType] || actionType;
    }

    /**
     * Send bulk action request
     */
    sendBulkAction(actionType, selectedItems) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/form-requests/bulk-${actionType}`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add selected items
        selectedItems.forEach(itemId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_items[]';
            input.value = itemId;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Initialize status updates
     */
    initStatusUpdates() {
        const statusUpdateElements = document.querySelectorAll('[data-status-update]');
        
        statusUpdateElements.forEach(element => {
            const requestId = element.dataset.requestId;
            if (requestId) {
                this.statusUpdates.set(requestId, element);
            }
        });
    }

    /**
     * Update status for a specific request
     */
    updateStatus(requestId, newStatus) {
        const element = this.statusUpdates.get(requestId);
        if (element) {
            element.textContent = newStatus;
            element.classList.remove('bg-warning', 'bg-info', 'bg-success');
            element.classList.add(`bg-${this.getStatusColor(newStatus)}`);
        }
    }

    /**
     * Get status color class
     */
    getStatusColor(status) {
        const statusColors = {
            'pending': 'warning',
            'acknowledged': 'info',
            'processing': 'info',
            'ready': 'success',
            'collected': 'success',
            'completed': 'success'
        };
        
        return statusColors[status.toLowerCase()] || 'secondary';
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.page-body .container-xl');
        if (container) {
            container.insertBefore(notification, container.firstChild);
        }

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    /**
     * Clean up resources
     */
    destroy() {
        this.slaTimers.forEach(timer => {
            if (timer.interval) {
                clearInterval(timer.interval);
            }
        });
        this.slaTimers.clear();
        this.statusUpdates.clear();
    }
}

// Initialize DMS Forms when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.dmsForms = new DMSForms();
});
