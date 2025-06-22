<!-- Confirmation Modal -->
<div id="confirmationModal" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(17, 24, 39, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
">
    <div id="modalContent" style="
        background: white;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 28rem;
        width: 100%;
        margin: 1rem;
        transform: scale(0.95);
        transition: transform 0.3s ease;
    ">
        <!-- Header -->
        <div style="
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 2rem;
            padding-bottom: 1rem;
        ">
            <div id="modalIconContainer" style="
                width: 4rem;
                height: 4rem;
                background: #dcfce7;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1rem;
            ">
                <svg id="modalIcon" style="
                    width: 2rem;
                    height: 2rem;
                    color: #16a34a;
                " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
        
        <!-- Content -->
        <div style="padding: 0 2rem 2rem 2rem;">
            <h3 id="modalTitle" style="
                font-size: 1.5rem;
                font-weight: 700;
                color: #111827;
                text-align: center;
                margin-bottom: 0.75rem;
                margin-top: 0;
            ">
                Mark as Completed
            </h3>
            
            <p id="modalSubtitle" style="
                color: #6b7280;
                text-align: center;
                margin-bottom: 1.5rem;
                font-size: 0.875rem;
                margin-top: 0;
            ">
                Payment completion confirmation
            </p>
            
            <p id="modalMessage" style="
                color: #374151;
                text-align: center;
                margin-bottom: 2rem;
                line-height: 1.6;
                margin-top: 0;
            ">
                Are you sure you want to mark this payment as completed?
            </p>
            
            <!-- Action Buttons -->
            <div style="
                display: flex;
                gap: 0.75rem;
                flex-direction: column;
            " id="buttonContainer">
                <button type="button" id="cancelButton" style="
                    flex: 1;
                    padding: 0.75rem 1.5rem;
                    background: white;
                    border: 2px solid #d1d5db;
                    color: #374151;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    font-size: 1rem;
                " onmouseover="
                    this.style.background='#f9fafb';
                    this.style.borderColor='#9ca3af';
                    this.style.transform='translateY(-1px)';
                " onmouseout="
                    this.style.background='white';
                    this.style.borderColor='#d1d5db';
                    this.style.transform='translateY(0)';
                " onclick="closeModal()">
                    Cancel
                </button>
                <button type="button" id="confirmButton" style="
                    flex: 1;
                    padding: 0.75rem 1.5rem;
                    background: #16a34a;
                    color: white;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                    border: none;
                    font-size: 1rem;
                " onmouseover="
                    this.style.background='#15803d';
                    this.style.transform='translateY(-1px)';
                    this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                " onmouseout="
                    this.style.background='#16a34a';
                    this.style.transform='translateY(0)';
                    this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                ">
                    Mark as Completed
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Animation */
#confirmationModal.show {
    display: flex !important;
}

#confirmationModal.show #modalContent {
    transform: scale(1) !important;
}

/* Responsive adjustments */
@media (min-width: 640px) {
    #buttonContainer {
        flex-direction: row !important;
    }
}

/* Focus states for accessibility */
#confirmButton:focus,
#cancelButton:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>

<script>
// Global variables for modal state
let confirmationCallback = null;
let confirmationRejectCallback = null;

function showModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    
    // Focus trap for accessibility
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.focus();
}

function closeModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    
    // Call reject callback if set
    if (confirmationRejectCallback) {
        confirmationRejectCallback();
    }
    
    // Reset callbacks
    confirmationCallback = null;
    confirmationRejectCallback = null;
}

function confirmAction() {
    if (confirmationCallback) {
        const result = confirmationCallback();
        
        if (result && typeof result.then === 'function') {
            result.finally(() => {
                closeModal();
            });
        } else {
            closeModal();
        }
    } else {
        closeModal();
    }
}

// Set up confirm button click handler
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmButton');
    if (confirmBtn) {
        confirmBtn.onclick = confirmAction;
    }
});

// Close modal when clicking outside
document.getElementById('confirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('confirmationModal');
        if (modal.classList.contains('show')) {
            closeModal();
        }
    }
});

// Main function for showing confirmation modal with options
function showConfirmationModal(options = {}) {
    const modal = document.getElementById('confirmationModal');
    const iconContainer = document.getElementById('modalIconContainer');
    const icon = document.getElementById('modalIcon');
    const title = document.getElementById('modalTitle');
    const subtitle = document.getElementById('modalSubtitle');
    const message = document.getElementById('modalMessage');
    const confirmBtn = document.getElementById('confirmButton');
    const cancelBtn = document.getElementById('cancelButton');

    const config = {
        title: 'Confirm Action',
        subtitle: 'This action requires confirmation',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'success',
        ...options
    };

    // Set content
    title.textContent = config.title;
    subtitle.textContent = config.subtitle;
    message.textContent = config.message;
    confirmBtn.textContent = config.confirmText;
    cancelBtn.textContent = config.cancelText;

    // Configure appearance based on type
    const typeConfig = {
        success: {
            iconBg: '#dcfce7',
            iconColor: '#16a34a',
            iconPath: 'M5 13l4 4L19 7',
            buttonBg: '#16a34a',
            buttonHover: '#15803d'
        },
        warning: {
            iconBg: '#fef3c7',
            iconColor: '#d97706',
            iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
            buttonBg: '#d97706',
            buttonHover: '#b45309'
        },
        danger: {
            iconBg: '#fee2e2',
            iconColor: '#dc2626',
            iconPath: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            buttonBg: '#dc2626',
            buttonHover: '#b91c1c'
        },
        info: {
            iconBg: '#dbeafe',
            iconColor: '#2563eb',
            iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            buttonBg: '#2563eb',
            buttonHover: '#1d4ed8'
        }
    };

    const currentType = typeConfig[config.type] || typeConfig.success;
    
    // Update icon and colors
    iconContainer.style.background = currentType.iconBg;
    icon.style.color = currentType.iconColor;
    icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${currentType.iconPath}"></path>`;
    
    // Update button styling
    confirmBtn.style.background = currentType.buttonBg;
    confirmBtn.onmouseover = function() {
        this.style.background = currentType.buttonHover;
        this.style.transform = 'translateY(-1px)';
        this.style.boxShadow = '0 20px 25px -5px rgba(0, 0, 0, 0.1)';
    };
    confirmBtn.onmouseout = function() {
        this.style.background = currentType.buttonBg;
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
    };

    // Set callbacks
    confirmationCallback = config.onConfirm;
    confirmationRejectCallback = config.onCancel;

    // Show modal
    showModal();
}

// Backward compatibility function for existing code
window.confirmPaymentAction = function(action, paymentCount = 1, options = {}) {
    const isPlural = paymentCount > 1;
    const paymentText = isPlural ? `${paymentCount} payments` : 'this payment';
    
    const actionConfig = {
        completed: {
            title: 'Mark as Completed',
            subtitle: 'Payment completion confirmation',
            message: `Are you sure you want to mark ${paymentText} as completed?`,
            confirmText: 'Mark as Completed',
            type: 'success'
        },
        failed: {
            title: 'Mark as Failed',
            subtitle: 'Payment failure confirmation',
            message: `Are you sure you want to mark ${paymentText} as failed?`,
            confirmText: 'Mark as Failed',
            type: 'danger'
        },
        delete: {
            title: 'Delete Payment',
            subtitle: 'Permanent deletion warning',
            message: `Are you sure you want to delete ${paymentText}?`,
            confirmText: 'Delete',
            type: 'danger'
        },
        notify: {
            title: 'Send Notification',
            subtitle: 'Email notification confirmation',
            message: `Send notification email${isPlural ? 's' : ''} for ${paymentText}?`,
            confirmText: 'Send Email',
            type: 'info'
        }
    };

    const config = actionConfig[action] || {
        title: 'Confirm Action',
        message: `Are you sure you want to proceed with this action on ${paymentText}?`,
        type: 'warning'
    };

    return new Promise((resolve) => {
        showConfirmationModal({
            ...config,
            ...options,
            onConfirm: () => resolve(true),
            onCancel: () => resolve(false)
        });
    });
};
</script>
