document.addEventListener('DOMContentLoaded', function() {
    const reportWidget = document.getElementById('reportWidget');
    if (!reportWidget) return;

    const toggleBtn = reportWidget.querySelector('.report-toggle-btn');
    const modal = reportWidget.querySelector('.report-modal');
    const closeBtn = reportWidget.querySelector('.report-close-btn');
    const form = reportWidget.querySelector('#reportForm');
    const typeSelect = reportWidget.querySelector('select[name="type"]');
    const categorySelect = reportWidget.querySelector('select[name="category"]');

    // Toggle modal
    function toggleModal() {
        const isActive = modal.classList.contains('active');
        if (isActive) {
            modal.classList.remove('active');
            toggleBtn.querySelector('i').setAttribute('data-lucide', 'message-square-warning');
        } else {
            modal.classList.add('active');
            toggleBtn.querySelector('i').setAttribute('data-lucide', 'x');
        }
        lucide.createIcons();
    }

    toggleBtn.addEventListener('click', toggleModal);
    closeBtn.addEventListener('click', toggleModal);

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (modal.classList.contains('active') && 
            !reportWidget.contains(e.target)) {
            toggleModal();
        }
    });

    // Dynamic categories based on type
    const categories = {
        'listing': ['Inappropriate Content', 'Misleading Information', 'Scam/Fraud', 'Duplicate Listing', 'Other'],
        'user': ['Harassment', 'Inappropriate Behavior', 'Spam', 'Fake Profile', 'Other'],
        'message': ['Spam', 'Harassment', 'Inappropriate Language', 'Scam Attempt', 'Other']
    };

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        const options = categories[type] || [];
        
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        options.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            categorySelect.appendChild(option);
        });
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader-2" class="animate-spin"></i> Sending...';
        lucide.createIcons();

        // Simulate API call
        setTimeout(() => {
            // Success state
            submitBtn.innerHTML = '<i data-lucide="check"></i> Sent!';
            lucide.createIcons();
            
            // Show toast
            if (window.showToast) {
                window.showToast('Report submitted successfully', 'success');
            } else {
                alert('Report submitted successfully');
            }

            // Reset and close
            setTimeout(() => {
                form.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                toggleModal();
            }, 1500);
        }, 1000);
    });
});
