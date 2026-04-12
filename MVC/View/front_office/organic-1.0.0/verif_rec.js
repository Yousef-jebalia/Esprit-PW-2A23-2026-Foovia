(function() {
  const form = document.querySelector('form');
  if (!form) {
    return;
  }
  form.noValidate = true;

  const fields = {
    id_recl: document.getElementById('id_recl'),
    id_user: document.getElementById('id_user'),
    description: document.getElementById('description'),
    etat_rec: document.getElementById('etat_rec'),
    type: document.getElementById('type')
  };

  const labels = {
    id_recl: 'ID Reclamation',
    id_user: 'ID User',
    description: 'Description',
    etat_rec: 'Etat Reclamation',
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
      case 'id_recl':
      case 'id_user':
        if (!value) {
          return `${labels[input.id]} is required.`;
        }
        if (!/^\d+$/.test(value)) {
          return `${labels[input.id]} must contain only numbers.`;
        }
        if (!/^\d{4}$/.test(value)) {
          return `${labels[input.id]} must be exactly 4 digits.`;
        }
        return '';
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
          return 'Etat Reclamation is required.';
        }
        if (value.length >= 10) {
          return 'Etat Reclamation must be less than 10 characters.';
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
