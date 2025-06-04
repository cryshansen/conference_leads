(function (Drupal) {
  Drupal.behaviors.tabNavigation = {
    attach: function (context, settings) {
      // Select all 'Next' buttons inside the tab panes
      const nextButtons = context.querySelectorAll('.next-tab');

      nextButtons.forEach(button => {
        // Add click event listener to each "Next" button
        button.addEventListener('click', function () {
          const nextTabId = this.getAttribute('data-next-tab');
          
          // Deactivate all tabs and content panes
          const navLinks = context.querySelectorAll('.nav-link');
          const tabPanes = context.querySelectorAll('.tab-pane');

          navLinks.forEach(tab => tab.classList.remove('active'));
          tabPanes.forEach(tabContent => tabContent.classList.remove('show', 'active'));
          
          // Activate the next tab and corresponding content pane
          const nextTabLink = context.querySelector(`[href="${nextTabId}"]`);
          const nextTabContent = context.querySelector(nextTabId);
          
          if (nextTabLink && nextTabContent) {
            nextTabLink.classList.add('active');
            nextTabContent.classList.add('show', 'active');
          }
        });
      });
    }
  };
})(Drupal);


/*
example from codepen  not necessary with drupal 
(function () {
'use strict'
const forms = document.querySelectorAll('.requires-validation')
Array.from(forms)
  .forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }

      form.classList.add('was-validated')
    }, false)
  })
})()


*/
