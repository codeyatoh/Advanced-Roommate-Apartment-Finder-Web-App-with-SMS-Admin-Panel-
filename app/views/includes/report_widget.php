<!-- Floating Report Widget -->
<link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/report-widget.module.css">

<div id="reportWidget" class="report-widget-container">
    <!-- Modal -->
    <div class="report-modal">
        <div class="report-header">
            <h3 class="report-title">Report an Issue</h3>
            <button class="report-close-btn">
                <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
            </button>
        </div>
        <div class="report-body">
            <form id="reportForm" class="report-form">
                <div class="form-group">
                    <label class="form-label">Report Type</label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="listing">Listing</option>
                        <option value="user">User</option>
                        <option value="message">Message</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Target (User/Listing Name)</label>
                    <input type="text" name="target" class="form-control" placeholder="e.g., John Doe or Modern Studio" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" placeholder="Please describe the issue..." required></textarea>
                </div>

                <div class="report-footer" style="padding: 0; background: transparent; border: none;">
                    <button type="submit" class="submit-btn">
                        <i data-lucide="send" style="width: 1rem; height: 1rem;"></i>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toggle Button -->
    <button class="report-toggle-btn">
        <i data-lucide="message-square-warning" style="width: 1.5rem; height: 1.5rem;"></i>
    </button>
</div>

<script src="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/js/report-widget.js"></script>
