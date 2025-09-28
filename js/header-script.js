/**
 * Handles the interactive role selection buttons in the header.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all the role selection buttons from the header
    const roleButtons = document.querySelectorAll('.role-navigation .role-btn');
    
    // Find the hidden input in your main login form that will store the selected role
    // IMPORTANT: Your main form must have an input with this ID
    const roleInput = document.getElementById('user_role_input');

    // Check if the buttons exist before adding the logic
    if (roleButtons.length > 0) {
        
        // Add a click event listener to each button
        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                
                // 1. Remove the 'active' class from all role buttons
                roleButtons.forEach(btn => btn.classList.remove('active'));
                
                // 2. Add the 'active' class to the specific button that was clicked
                this.classList.add('active');
                
                // 3. (Optional but recommended) Update the hidden form input's value
                if (roleInput) {
                    roleInput.value = this.getAttribute('data-role');
                }
            });
        });
    }
});
