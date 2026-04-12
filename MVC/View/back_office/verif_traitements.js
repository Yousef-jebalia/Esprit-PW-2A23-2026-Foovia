(function() {
  const form = document.querySelector('form');
  if (!form) {
    return;
  }
  form.noValidate = true;

  const fields = {
    id_traitement: document.getElementById('id_traitement'),
    id_reclam: document.getElementById('id_reclam'),
    id_user: document.getElementById('id_user'),
    comment: document.getElementById('comment'),
    status: document.getElementById('status')
  };

  const labels = {
    id_traitement: 'ID Traitement',
    id_reclam: 'ID Réclamation',
    id_user: 'ID User',
    comment: 'Commentaire',
    status: 'Status'
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
      case 'id_traitement':
      case 'id_reclam':
      case 'id_user':
        if (!value) {
          return `${labels[input.id]} est requis.`;
        }
        if (!/^\d+$/.test(value)) {
          return `${labels[input.id]} doit contenir uniquement des chiffres.`;
        }
        if (!/^\d{4}$/.test(value)) {
          return `${labels[input.id]} doit comporter exactement 4 chiffres.`;
        }
        return '';
      case 'comment':
        if (!value) {
          return 'Commentaire est requis.';
        }
        if (value.length >= 250) {
          return `Commentaire doit être inférieur à 250 caractères. (${value.length})`;
        }
        return '';
      case 'status':
        if (!value) {
          return 'Status est requis.';
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
