
(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach( function(e) {
    e.querySelectorAll('input, textarea').forEach(function(field) {
      const error = document.createElement('div');
      error.className = 'field-error';
      error.setAttribute('aria-live', 'polite');
      field.insertAdjacentElement('afterend', error);
      field.addEventListener('input', function() {
        validateField(field);
      });
    });

    e.addEventListener('submit', function(event) {
      event.preventDefault();

      let thisForm = this;

      const invalidFields = Array.from(thisForm.querySelectorAll('input, textarea'))
        .filter(function(field) {
          return !validateField(field);
        });

      if (invalidFields.length > 0) {
        invalidFields[0].focus();
        return;
      }

      let action = thisForm.getAttribute('action');
      let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');
      
      if( ! action ) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }
      thisForm.querySelector('.loading').classList.add('d-block');
      thisForm.querySelector('.error-message').classList.remove('d-block');
      thisForm.querySelector('.sent-message').classList.remove('d-block');

      let formData = new FormData( thisForm );

      if ( recaptcha ) {
        if(typeof grecaptcha !== "undefined" ) {
          grecaptcha.ready(function() {
            try {
              grecaptcha.execute(recaptcha, {action: 'php_email_form_submit'})
              .then(token => {
                formData.set('recaptcha-response', token);
                php_email_form_submit(thisForm, action, formData);
              })
            } catch(error) {
              displayError(thisForm, error);
            }
          });
        } else {
          displayError(thisForm, 'The reCaptcha javascript API url is not loaded!')
        }
      } else {
        php_email_form_submit(thisForm, action, formData);
      }
    });
  });

  function validateField(field) {
    const error = field.nextElementSibling;
    let message = '';

    if (!field.value.trim()) {
      message = field.placeholder + ' is required.';
    } else if (field.type === 'email' && !field.validity.valid) {
      message = 'Enter a valid email address.';
    }

    error.textContent = message;
    error.classList.toggle('is-visible', Boolean(message));
    field.classList.toggle('is-invalid', Boolean(message));
    return !message;
  }

  function php_email_form_submit(thisForm, action, formData) {
    fetch(action, {
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
      if( response.ok ) {
        return response.text();
      } else {
        throw new Error(`${response.status} ${response.statusText} ${response.url}`); 
      }
    })
    .then(data => {
      thisForm.querySelector('.loading').classList.remove('d-block');
      if (data.trim() == 'OK') {
        thisForm.reset(); 
        displaySuccess();
      } else {
        throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action); 
      }
    })
    .catch((error) => {
      displayError(thisForm, error);
    });
  }

  function displaySuccess() {
    if (typeof swal === 'function') {
      swal('Message sent', 'Thank you. We will get back to you soon.', 'success');
    }
  }

  function displayError(thisForm, error) {
    thisForm.querySelector('.loading').classList.remove('d-block');
    if (typeof swal === 'function') {
      swal('Message not sent', error.message || String(error), 'error');
    }
  }

})();
