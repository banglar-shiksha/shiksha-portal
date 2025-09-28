/**
 * Handles the interactive role selection on the login page.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all the role selection buttons
    const roleButtons = document.querySelectorAll('.role-btn');
    
    // Get the hidden input field that stores the selected role
    const roleInput = document.getElementById('user_role_input');

    // Ensure both buttons and the input field exist before adding listeners
    if (roleButtons.length > 0 && roleInput) {
        
        // Add a click event listener to each role button
        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                
                // 1. Remove the 'active' class from all buttons
                roleButtons.forEach(btn => btn.classList.remove('active'));
                
                // 2. Add the 'active' class to the button that was just clicked
                this.classList.add('active');
                
                // 3. Update the hidden input's value with the role from the 'data-role' attribute
                roleInput.value = this.getAttribute('data-role');
            });
        });
    }
});
