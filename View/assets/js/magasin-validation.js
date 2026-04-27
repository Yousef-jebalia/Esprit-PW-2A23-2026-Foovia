document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-store-form]');

  if (!form) return;

  const fields = {
    name_mag: form.querySelector('[name="name_mag"]'),
    email_mag: form.querySelector('[name="email_mag"]'),
    phone_mag: form.querySelector('[name="phone_mag"]'),
    adress_mag: form.querySelector('[name="adress_mag"]'),
    img_mag: form.querySelector('[name="img_mag"]')
  };

  const preview = document.querySelector('[data-store-image-preview]');

  const setError = (fieldName, message) => {
    const errorNode = form.querySelector(`[data-error-for="${fieldName}"]`);
    if (errorNode) errorNode.textContent = message;
  };

  const clearError = (fieldName) => setError(fieldName, '');

  const validators = {
    name_mag: () => {
      const value = fields.name_mag.value.trim();
      if (!value) {
        setError('name_mag', 'Magasin name is required.');
        return false;
      }
      if (value.length > 120) {
        setError('name_mag', 'Magasin name must stay within 120 characters because of the SQL schema.');
        return false;
      }
      clearError('name_mag');
      return true;
    },
    email_mag: () => {
      const value = fields.email_mag.value.trim();
      if (!value) {
        setError('email_mag', 'Email is required.');
        return false;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        setError('email_mag', 'Enter a valid email address.');
        return false;
      }
      if (value.length > 191) {
        setError('email_mag', 'Email must stay within 191 characters because of the SQL schema.');
        return false;
      }
      clearError('email_mag');
      return true;
    },
    phone_mag: () => {
      const value = fields.phone_mag.value.trim();
      if (!value) {
        setError('phone_mag', 'Phone number is required.');
        return false;
      }
      if (!/^\d{8,15}$/.test(value)) {
        setError('phone_mag', 'Phone number must contain 8 to 15 digits.');
        return false;
      }
      if (value.length > 30) {
        setError('phone_mag', 'Phone number must stay within 30 characters because of the SQL schema.');
        return false;
      }
      clearError('phone_mag');
      return true;
    },
    adress_mag: () => {
      const value = fields.adress_mag.value.trim();
      if (!value) {
        setError('adress_mag', 'Address is required.');
        return false;
      }
      if (value.length > 255) {
        setError('adress_mag', 'Address must stay within 255 characters because of the SQL schema.');
        return false;
      }
      clearError('adress_mag');
      return true;
    },
    img_mag: () => {
      const file = fields.img_mag.files[0];
      if (!file) {
        clearError('img_mag');
        return true;
      }
      if (!file.type.startsWith('image/')) {
        setError('img_mag', 'The selected file must be an image.');
        return false;
      }
      clearError('img_mag');
      return true;
    }
  };

  Object.entries(fields).forEach(([name, field]) => {
    if (!field) return;
    const eventName = field.type === 'file' ? 'change' : 'input';
    field.addEventListener(eventName, () => validators[name]());
  });

  fields.img_mag.addEventListener('change', () => {
    const file = fields.img_mag.files[0];
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      preview.innerHTML = `<img src="${event.target?.result}" alt="Magasin preview">`;
    };
    reader.readAsDataURL(file);
  });

  form.addEventListener('submit', (event) => {
    const isValid = Object.values(validators).every((validator) => validator());
    if (!isValid) event.preventDefault();
  });
});
