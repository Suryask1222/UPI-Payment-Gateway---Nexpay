// Administrative Panel JavaScript Controller
const AdminApp = {
    init: function() {
        this.initTheme();
        this.registerEventListeners();
    },

    // Handles dark/light theme setting and toggle change
    initTheme: function() {
        const currentTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        const toggleSwitch = document.getElementById('theme-toggle-input');
        if (toggleSwitch) {
            toggleSwitch.checked = (currentTheme === 'dark');
            toggleSwitch.addEventListener('change', (e) => {
                const newTheme = e.target.checked ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
        }
    },

    // Listeners for modals and triggers
    registerEventListeners: function() {
        const modalOverlays = document.querySelectorAll('.modal-overlay');
        modalOverlays.forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.closeModal(overlay.id);
                }
            });
        });
    },

    openModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    },

    closeModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    },

    // Opens Quick Action Modal for transaction approval/rejection
    triggerActionModal: function(orderNo, actionType, currentUtr) {
        document.getElementById('modal-order-no').innerText = orderNo;
        document.getElementById('form-action-order').value = orderNo;
        document.getElementById('form-action-type').value = actionType;
        document.getElementById('form-action-utr').value = currentUtr || '';
        
        const actionLabel = document.getElementById('modal-action-label');
        const submitBtn = document.getElementById('btn-modal-action-submit');
        
        submitBtn.className = 'btn';
        if (actionType === 'approved') {
            actionLabel.innerText = 'Approve Payment';
            submitBtn.classList.add('btn-success');
            submitBtn.innerText = 'Verify & Approve';
        } else if (actionType === 'rejected') {
            actionLabel.innerText = 'Reject Payment';
            submitBtn.classList.add('btn-danger');
            submitBtn.innerText = 'Confirm Rejection';
        } else if (actionType === 'on_hold') {
            actionLabel.innerText = 'Place on Hold';
            submitBtn.classList.add('btn-primary');
            submitBtn.innerText = 'Place on Hold';
        } else if (actionType === 'under_review') {
            actionLabel.innerText = 'Place under Review';
            submitBtn.classList.add('btn-warning');
            submitBtn.innerText = 'Move to Review';
        }

        this.openModal('action-dialog-modal');
    },

    // Submits the transaction approval form
    submitActionForm: function(event) {
        event.preventDefault();
        
        const form = event.target;
        const btn = document.getElementById('btn-modal-action-submit');
        const orderNo = document.getElementById('form-action-order').value;
        const action = document.getElementById('form-action-type').value;
        const note = document.getElementById('form-action-note').value;
        const utr = document.getElementById('form-action-utr').value;

        btn.disabled = true;
        const originalText = btn.innerText;
        btn.innerHTML = '<span class="spinner"></span> Processing...';

        const formData = new FormData();
        formData.append('order', orderNo);
        formData.append('action', action);
        formData.append('note', note);
        formData.append('utr', utr);

        fetch(BASE_URL + '/index.php?route=/admin/transactions/action', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.closeModal('action-dialog-modal');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update transaction status.');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            console.error('Action submission error: ', err);
            alert('A connection error occurred. Please try again.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    },

    // Toggles the active status of a UPI account
    toggleUpi: function(id, activeState) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('active', activeState);

        fetch(BASE_URL + '/index.php?route=/admin/upi/toggle', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to toggle UPI status.');
            }
        });
    },

    // Deletes a UPI account from the rotator list
    deleteUpi: function(id) {
        if (!confirm('Are you absolutely sure you want to delete this UPI ID? This will remove it from rotations permanently.')) return;

        const formData = new FormData();
        formData.append('id', id);

        fetch(BASE_URL + '/index.php?route=/admin/upi/delete', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete UPI Account.');
            }
        });
    },

    // Sets a default UPI target
    setUpiDefault: function(id) {
        const formData = new FormData();
        formData.append('id', id);

        fetch(BASE_URL + '/index.php?route=/admin/upi/default', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to update default UPI settings.');
            }
        });
    },

    openAddUpiModal: function() {
        document.getElementById('upi-modal-title').innerText = 'Add Rotatable UPI ID';
        document.getElementById('upi-form-op').value = 'add';
        document.getElementById('upi-form-id').value = '';
        document.getElementById('upi-form-id-field').value = '';
        document.getElementById('upi-form-name').value = '';
        document.getElementById('upi-form-purpose').value = 'all';
        
        this.openModal('upi-form-modal');
    },

    openEditUpiModal: function(id, upiId, payeeName, purpose) {
        document.getElementById('upi-modal-title').innerText = 'Edit UPI Configuration';
        document.getElementById('upi-form-op').value = 'edit';
        document.getElementById('upi-form-id').value = id;
        document.getElementById('upi-form-id-field').value = upiId;
        document.getElementById('upi-form-name').value = payeeName;
        document.getElementById('upi-form-purpose').value = purpose;
        
        this.openModal('upi-form-modal');
    },

    // Saves changes to UPI configuration
    submitUpiForm: function(event) {
        event.preventDefault();
        
        const op = document.getElementById('upi-form-op').value;
        const id = document.getElementById('upi-form-id').value;
        const upiId = document.getElementById('upi-form-id-field').value.trim();
        const payeeName = document.getElementById('upi-form-name').value.trim();
        const purpose = document.getElementById('upi-form-purpose').value;
        const btn = document.getElementById('btn-upi-submit');

        if (!upiId || !payeeName) {
            alert('All fields are required.');
            return;
        }

        btn.disabled = true;
        const originalText = btn.innerText;
        btn.innerHTML = '<span class="spinner"></span> Saving...';

        const formData = new FormData();
        if (op === 'edit') {
            formData.append('id', id);
        }
        formData.append('upi_id', upiId);
        formData.append('payee', payeeName);
        formData.append('purpose', purpose);

        const url = BASE_URL + '/index.php?route=/admin/upi/' + op;

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.closeModal('upi-form-modal');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to save UPI account settings.');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            console.error('UPI submit error: ', err);
            alert('A connection error occurred.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AdminApp.init();
});