(function() {
  const form = document.querySelector('form');
  if (!form) {
    return;
  }
  form.noValidate = true;

  const fields = {
    description: document.getElementById('description'),
    etat_rec: document.getElementById('etat_rec'),
    type: document.getElementById('type')
  };

  const labels = {
    description: 'Description',
    etat_rec: 'Claim Status',
    type: 'Type'
  };

  function createBubble(input) {
    let bubble = input.parentNode.querySelector('.validation-bubble');
    if (!bubble) {
      bubble = document.createElement('span');
      bubble.className = 'validation-bubble';
      bubble.style.color = '#d93025';
      bubble.style.fontSize = '0.9rem';
      bubble.style.marginTop = '0.25rem';
      bubble.style.display = 'block';
      bubble.style.lineHeight = '1.2';
      bubble.style.minHeight = '1.2em';
      input.parentNode.appendChild(bubble);
    }
    return bubble;
  }

  function showError(input, message) {
    const bubble = createBubble(input);
    bubble.textContent = message;
    input.style.borderColor = '#d93025';
    input.style.outline = 'none';
  }

  function clearError(input) {
    const bubble = input.parentNode.querySelector('.validation-bubble');
    if (bubble) {
      bubble.textContent = '';
    }
    input.style.borderColor = '';
    input.style.outline = '';
  }

  function getErrorMessage(input) {
    const value = input.value.trim();
    switch (input.id) {
      case 'description':
        if (!value) {
          return 'Description is required.';
        }
        if (value.length >= 400) {
          return `Description must be less than 400 characters. (${value.length})`;
        }
        return '';
      case 'etat_rec':
        if (!value) {
          return 'Claim Status is required.';
        }
        if (value.length >= 10) {
          return 'Claim Status must be less than 10 characters.';
        }
        return '';
      case 'type':
        if (!value) {
          return 'Type is required.';
        }
        return '';
      default:
        return '';
    }
  }

  function validateField(input) {
    if (!input) {
      return true;
    }
    const message = getErrorMessage(input);
    if (message) {
      showError(input, message);
      return false;
    }
    clearError(input);
    return true;
  }

  function validateAllFields() {
    let valid = true;
    Object.values(fields).forEach((input) => {
      if (!input) {
        return;
      }
      if (!validateField(input)) {
        valid = false;
      }
    });
    return valid;
  }

  Object.values(fields).forEach((input) => {
    if (!input) {
      return;
    }
    input.addEventListener('input', () => validateField(input));
    input.addEventListener('blur', () => validateField(input));
  });

  form.addEventListener('submit', (event) => {
    const valid = validateAllFields();
    if (!valid) {
      event.preventDefault();
      event.stopImmediatePropagation();
      const firstInvalid = Object.values(fields).find((input) => {
        return input && input.parentNode.querySelector('.validation-bubble')?.textContent;
      });
      if (firstInvalid) {
        firstInvalid.focus();
      }
    }
  });
})();
