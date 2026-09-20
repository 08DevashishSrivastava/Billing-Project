/**
 * FinPilot - Personal Finance Decision Support Agent
 */

// Toast Notification System
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };
    
    const colors = {
        success: 'bg-green-500/20 border-green-500/50 text-green-200',
        error: 'bg-red-500/20 border-red-500/50 text-red-200',
        warning: 'bg-yellow-500/20 border-yellow-500/50 text-yellow-200',
        info: 'bg-blue-500/20 border-blue-500/50 text-blue-200'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast glass-card ${colors[type]} px-4 py-3 rounded-xl flex items-center gap-3 min-w-72 shadow-lg border`;
    toast.innerHTML = `
        <i class="bi ${icons[type]} text-lg"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="hover:opacity-70 transition">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Confirmation Modal
function confirmAction(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="modal-backdrop absolute inset-0" onclick="this.parentElement.remove()"></div>
        <div class="modal-content relative rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-3xl text-red-400"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Confirm Action</h3>
                <p class="text-white/70 mb-6">${message}</p>
                <div class="flex gap-3 justify-center">
                    <button onclick="this.closest('.fixed').remove()" class="btn-secondary px-6 py-2 rounded-xl">
                        Cancel
                    </button>
                    <button onclick="confirmCallback()" class="bg-red-500 hover:bg-red-600 px-6 py-2 rounded-xl transition">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.confirmCallback = function() {
        modal.remove();
        callback();
    };
}

// Delete form submission with confirmation
function deleteItem(formId, message = 'Are you sure you want to delete this item?') {
    confirmAction(message, function() {
        document.getElementById(formId).submit();
    });
}

// Format currency
function formatCurrency(amount, symbol = '$') {
    return symbol + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// AJAX Request Helper
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showToast('An error occurred. Please try again.', 'error');
        throw error;
    }
}

// Form validation helper
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            
            // Show error message
            let error = input.nextElementSibling;
            if (!error || !error.classList.contains('error-message')) {
                error = document.createElement('p');
                error.className = 'error-message text-red-400 text-sm mt-1';
                input.parentNode.insertBefore(error, input.nextSibling);
            }
            error.textContent = 'This field is required';
        } else {
            input.classList.remove('border-red-500');
            const error = input.nextElementSibling;
            if (error && error.classList.contains('error-message')) {
                error.remove();
            }
        }
    });
    
    return isValid;
}

// Print functionality
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            ${element.innerHTML}
            <script>window.onload = function() { window.print(); window.close(); }<\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Table search/filter
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (!input || !table) return;
    
    input.addEventListener('input', debounce(function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }, 300));
}

// Number formatting for inputs
function formatNumberInput(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/[^\d.]/g, '');
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].slice(0, 2);
        }
        this.value = value;
    });
}

// Initialize tooltips
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-xs bg-gray-900 text-white rounded shadow-lg';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.bottom = '100%';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translateX(-50%) translateY(-4px)';
            this.style.position = 'relative';
            this.appendChild(tooltip);
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.absolute');
            if (tooltip) tooltip.remove();
        });
    });
}

// Auto-resize textarea
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Auto-resize textareas
    document.querySelectorAll('textarea[data-autoresize]').forEach(textarea => {
        textarea.addEventListener('input', () => autoResizeTextarea(textarea));
        autoResizeTextarea(textarea);
    });
    
    // Format number inputs
    document.querySelectorAll('input[data-type="currency"]').forEach(formatNumberInput);
    
    // Initialize table filters
    const searchInputs = document.querySelectorAll('[data-table-filter]');
    searchInputs.forEach(input => {
        filterTable(input.id, input.dataset.tableFilter);
    });
});

// Export functions for global use
window.showToast = showToast;
window.confirmAction = confirmAction;
window.deleteItem = deleteItem;
window.formatCurrency = formatCurrency;
window.fetchData = fetchData;
window.printElement = printElement;
