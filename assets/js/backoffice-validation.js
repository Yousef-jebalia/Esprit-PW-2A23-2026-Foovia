document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-product-form]');

  if (!form) return;

  const fields = {
    id_mag: form.querySelector('[name="id_mag"]'),
    name_march: form.querySelector('[name="name_march"]'),
    description_march: form.querySelector('[name="description_march"]'),
    price_march: form.querySelector('[name="price_march"]'),
    quantity_march: form.querySelector('[name="quantity_march"]'),
    date_expiration_march: form.querySelector('[name="date_expiration_march"]'),
    point_acces_march: form.querySelector('[name="point_acces_march"]'),
    img_march: form.querySelector('[name="img_march"]')
  };

  const preview = document.querySelector('[data-image-preview]');
  const today = new Date().toISOString().split('T')[0];
  const editingId = Number(form.querySelector('[name="id_march"]')?.value || 0);
  const isEditingMode = form.dataset.editingMode === 'true' || editingId > 0;

  const setError = (fieldName, message) => {
    const errorNode = form.querySelector(`[data-error-for="${fieldName}"]`);
    if (errorNode) errorNode.textContent = message;
  };

  const clearError = (fieldName) => setError(fieldName, '');

  const validators = {
    id_mag: () => {
      if (!fields.id_mag.value) {
        setError('id_mag', 'Choose the magasin that will sell this product.');
        return false;
      }
      clearError('id_mag');
      return true;
    },
    name_march: () => {
      const value = fields.name_march.value.trim();
      if (!value) {
        setError('name_march', 'Product name is required.');
        return false;
      }
      if (value.length > 10) {
        setError('name_march', 'Name must stay within 10 characters because of the SQL schema.');
        return false;
      }
      clearError('name_march');
      return true;
    },
    description_march: () => {
      const value = fields.description_march.value.trim();
      if (!value) {
        setError('description_march', 'Description is required.');
        return false;
      }
      if (value.length > 50) {
        setError('description_march', 'Description must stay within 50 characters.');
        return false;
      }
      clearError('description_march');
      return true;
    },
    price_march: () => {
      const value = fields.price_march.value.trim();
      if (!value) {
        setError('price_march', 'Price is required.');
        return false;
      }
      if (!/^\d+$/.test(value)) {
        setError('price_march', 'Price must be a positive integer.');
        return false;
      }
      clearError('price_march');
      return true;
    },
    quantity_march: () => {
      const value = fields.quantity_march.value.trim();
      if (!value) {
        setError('quantity_march', 'Quantity is required.');
        return false;
      }
      if (!/^\d+$/.test(value)) {
        setError('quantity_march', 'Quantity must be a positive integer.');
        return false;
      }
      clearError('quantity_march');
      return true;
    },
    date_expiration_march: () => {
      const value = fields.date_expiration_march.value;
      if (!value) {
        setError('date_expiration_march', 'Expiration date is required.');
        return false;
      }
      if (value < today) {
        setError('date_expiration_march', 'Expiration date must be today or later.');
        return false;
      }
      clearError('date_expiration_march');
      return true;
    },
    point_acces_march: () => {
      const value = fields.point_acces_march.value.trim();
      if (!value) {
        setError('point_acces_march', 'Point d\'acces is required.');
        return false;
      }
      if (value.length > 10) {
        setError('point_acces_march', 'Point d\'acces must stay within 10 characters.');
        return false;
      }
      clearError('point_acces_march');
      return true;
    },
    img_march: () => {
      const file = fields.img_march.files[0];
      const hasExistingImage = Boolean(preview && preview.querySelector('img'));
      if (!file && (isEditingMode || hasExistingImage)) {
        clearError('img_march');
        return true;
      }
      if (!file) {
        setError('img_march', 'Product image is required.');
        return false;
      }
      if (!file.type.startsWith('image/')) {
        setError('img_march', 'The selected file must be an image.');
        return false;
      }
      clearError('img_march');
      return true;
    }
  };

  Object.entries(fields).forEach(([name, field]) => {
    if (!field) return;
    const eventName = field.type === 'file' || field.tagName === 'SELECT' ? 'change' : 'input';
    field.addEventListener(eventName, () => validators[name]());
  });

  fields.img_march.addEventListener('change', () => {
    const file = fields.img_march.files[0];
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      preview.innerHTML = `<img src="${event.target?.result}" alt="Product preview">`;
    };
    reader.readAsDataURL(file);
  });

  form.addEventListener('submit', (event) => {
    const isValid = Object.values(validators).every((validator) => validator());
    if (!isValid) event.preventDefault();
  });
});
