jQuery(document).ready(function($) {
    // Handle search button click
    $('#search_advisor_btn').on('click', function() {
        performAdvisorSearch();
    });

    // Also handle Enter key in the postal code field
    $('#postal_code_input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            performAdvisorSearch();
        }
    });

    function performAdvisorSearch() {
        var postalCode = $('#postal_code_input').val().trim();
        var $resultsContainer = $('#advisor_results');

        // Validate postal code (10 digits)
        if (!postalCode || postalCode.length !== 10 || !/^\d{10}$/.test(postalCode)) {
            $resultsContainer.html('<div class="error-message">Please enter a valid 10-digit postal code</div>');
            return;
        }

        // Show loading message
        $resultsContainer.html('<div class="loading">Searching...</div>');

        // Prepare data for AJAX request
        var data = {
            action: 'handle_postal_code_search',
            nonce: team_members_ajax.nonce,
            postal_code: postalCode,
            department_id: departmentId,
            count: resultCount
        };

        // Make AJAX request
        $.post(team_members_ajax.ajax_url, data, function(response) {
            if (response.success) {
                $resultsContainer.html(response.data);
            } else {
                $resultsContainer.html('<div class="error-message">' + response.data + '</div>');
            }
        }).fail(function() {
            $resultsContainer.html('<div class="error-message">An error occurred while searching. Please try again.</div>');
        });
    }
});