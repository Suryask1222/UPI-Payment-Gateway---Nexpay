/**
 * Nex Pay - Customer Gateway JavaScript Controller
 */

const Gateway = {
    /**
     * Copy text to clipboard and show visual response on target element.
     */
    copyToClipboard: function(text, elementId) {
        navigator.clipboard.writeText(text).then(() => {
            const el = document.getElementById(elementId);
            if (!el) return;

            const originalContent = el.innerHTML;
            el.innerHTML = '<span style="color:var(--success); font-weight:700;">COPIED!</span>';
            el.style.borderColor = 'var(--success)';
            
            setTimeout(() => {
                el.innerHTML = originalContent;
                el.style.borderColor = '';
            }, 2000);
        }).catch(err => {
            console.error('Clipboard copy failed: ', err);
        });
    },

    /**
     * Initialize QRCode render using QRCode.js library.
     */
    initQR: function(elementId, text) {
        if (!text || !document.getElementById(elementId)) return;
        
        // Clear container first
        document.getElementById(elementId).innerHTML = "";
        
        try {
            new QRCode(document.getElementById(elementId), {
                text: text,
                width: 180,
                height: 180,
                colorDark: "#0b0f19",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (err) {
            console.error("QR Code generation failed: ", err);
        }
    },

    /**
     * Redirect to Paytm payment flow (Smart Pay direct redirection)
     */
    payWithPaytm: function(mobile, upiId, amount, orderNo) {
        const message = 'Order ' + orderNo;
        // 1. Copy to clipboard immediately
        navigator.clipboard.writeText(upiId);

        // 2. Visual feedback on button (Faster than alert)
        const btn = document.querySelector('.btn-paytm');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening Paytm...';
            btn.disabled = true;
        }

        // 3. Combined Automated Deep Link (uucashier pattern)
        const payUrl = 'paytmmp://cash_wallet?featuretype=money_transfer&pa=' + encodeURIComponent(upiId) +
            '&pn=' + encodeURIComponent('Nexentora Technologies') +
            '&am=' + amount +
            '&tn=' + encodeURIComponent(message) +
            '&cu=INR&mc=7622&mode=02&orgid=159002&purpose=00';

        // Direct redirection to the app context
        location.replace(payUrl);

        // Reset button after a delay (if user comes back)
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }, 3000);
    },

    /**
     * Redirect to PhonePe payment flow (Smart Pay PhonePe direct redirection)
     */
    payWithPhonePe: function(upiId, amount, orderNo) {
        const message = 'Order ' + orderNo;
        const btn = document.querySelector('.btn-phonepe');
        const originalHtml = btn ? btn.innerHTML : '';

        // 1. Copy to clipboard immediately
        navigator.clipboard.writeText(upiId);

        // 2. Visual feedback on button
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening PhonePe...';
            btn.disabled = true;
        }

        // 3. Automated Deep Link (Native Base64 Scheme)
        const phonePeData = {
            contactInfo: { type: "VPA", vpa: upiId, cbsName: "773422013" },
            sendParams: { initialAmount: parseInt(parseFloat(amount) * 100), note: message },
            validateDestination: false,
            withSheetExpanded: true,
            shouldShowUnsavedBanner: false
        };
        const base64Data = btoa(JSON.stringify(phonePeData));
        const payUrl = `phonepe://native?data=${base64Data}&id=p2pContactChat`;

        // Direct redirection to the app context
        location.replace(payUrl);

        // Reset button after a delay
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }, 3000);
    },

    /**
     * Redirect to Google Pay (GPay) payment flow (Smart Pay GPay direct redirection)
     */
    payWithGPay: function(upiId, payeeName, amount, orderNo) {
        const btn = document.querySelector('.btn-gpay');
        const originalHtml = btn ? btn.innerHTML : '';

        // 1. Copy to clipboard immediately as fallback
        navigator.clipboard.writeText(upiId);

        // 2. Visual feedback on button
        if (btn) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening Google Pay...';
            btn.disabled = true;
        }

        // 3. Construct standard UPI deep link (prefilled ID, Name, and Amount)
        // This invokes the system UPI chooser/launcher which bypasses browser-specific package blocking
        const payUrl = 'upi://pay?pa=' + encodeURIComponent(upiId) +
                       '&pn=' + encodeURIComponent(payeeName) +
                       '&am=' + amount +
                       '&cu=INR';

        // Direct redirection to the app context
        location.replace(payUrl);

        // Reset button after a delay
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }, 3000);
    },

    /**
     * Submits the 12-digit transaction UTR number to backend.
     */
    submitUTR: function(orderNo) {
        const utr = document.getElementById('utr_input').value.trim();
        const name = document.getElementById('payer_name').value.trim();
        const notes = document.getElementById('payer_notes').value.trim();
        const btn = document.getElementById('btn-submit-verification');

        if (!name || !utr) {
            alert('Please enter both Payer Full Name and 12-digit UTR/Reference ID.');
            return;
        }
        if (utr.length !== 12 || !/^\d{12}$/.test(utr)) {
            alert('Please enter a valid 12-digit numeric UTR/Reference ID.');
            return;
        }

        btn.disabled = true;
        const originalText = btn.innerText;
        btn.innerHTML = '<span class="spinner"></span> Submitting...';

        const formData = new FormData();
        formData.append('order', orderNo);
        formData.append('utr', utr);
        formData.append('name', name);
        formData.append('notes', notes);

        // Fetch post to local API via index.php parameter routing
        fetch(BASE_URL + '/index.php?route=/api/submit-utr', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Verification submission failed. Please try again.');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            console.error('Submission connection error: ', err);
            alert('A connection error occurred. Please verify your internet and try again.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    },

    /**
     * Poll transaction status from backend and reload page upon updates.
     */
    startStatusPolling: function(orderNo) {
        setInterval(() => {
            fetch(`${BASE_URL}/index.php?route=/pay/status&order=${orderNo}`)
            .then(res => res.json())
            .then(data => {
                if (data.status && data.status !== 'pending' && data.status !== 'under_review') {
                    window.location.reload();
                }
            })
            .catch(err => {
                console.warn('Status polling error: ', err);
            });
        }, 5000);
    }
};