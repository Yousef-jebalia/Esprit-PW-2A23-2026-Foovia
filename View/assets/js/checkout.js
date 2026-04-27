document.addEventListener('DOMContentLoaded', () => {
  const cartStorageKey = 'fooviaCartItems';
  const cart = (() => {
    try {
      const parsed = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  })();

  const form = document.querySelector('[data-checkout-form]');
  const itemsWrap = document.querySelector('[data-checkout-items]');
  const subtotalNode = document.querySelector('[data-checkout-subtotal]');
  const deliveryNode = document.querySelector('[data-checkout-delivery]');
  const feeNode = document.querySelector('[data-checkout-fee]');
  const totalNode = document.querySelector('[data-checkout-total]');
  const emptyNode = document.querySelector('[data-checkout-empty]');
  const processingModal = document.querySelector('[data-payment-processing]');
  const successModal = document.querySelector('[data-payment-success]');
  const successReference = document.querySelector('[data-success-reference]');

  const holderField = document.querySelector('[data-field="holder_name"]');
  const emailField = document.querySelector('[data-field="email"]');
  const numberField = document.querySelector('[data-field="card_number"]');
  const expiryField = document.querySelector('[data-field="expiry"]');
  const cvvField = document.querySelector('[data-field="cvv"]');
  const phoneField = document.querySelector('[data-field="phone"]');
  const addressField = document.querySelector('[data-field="address"]');
  const cityField = document.querySelector('[data-field="city"]');
  const postalField = document.querySelector('[data-field="postal_code"]');
  const countryField = document.querySelector('[data-field="country"]');

  const previewName = document.querySelector('[data-card-preview-name]');
  const previewNumber = document.querySelector('[data-card-preview-number]');
  const previewExpiry = document.querySelector('[data-card-preview-expiry]');
  const previewBrand = document.querySelector('[data-card-brand]');

  const formatPrice = (value) => {
    const price = Number(value || 0);
    return `${price.toFixed(3).replace(/\.?0+$/, '')} TND`;
  };

  const setError = (field, message) => {
    const errorNode = document.querySelector(`[data-error-for="${field}"]`);
    if (errorNode) {
      errorNode.textContent = message;
    }
  };

  const clearErrors = () => {
    document.querySelectorAll('[data-error-for]').forEach((node) => {
      node.textContent = '';
    });
  };

  const detectCardBrand = (digits) => {
    if (/^4/.test(digits)) return 'VISA';
    if (/^(5[1-5]|2[2-7])/.test(digits)) return 'MASTERCARD';
    if (/^3[47]/.test(digits)) return 'AMEX';
    return 'CARD';
  };

  const renderSummary = () => {
    if (!itemsWrap || !subtotalNode || !deliveryNode || !feeNode || !totalNode) {
      return;
    }

    const subtotal = cart.reduce((sum, item) => sum + (Number(item.price || 0) * Number(item.quantity || 0)), 0);
    const delivery = cart.length > 0 ? 7.5 : 0;
    const fee = cart.length > 0 ? 1.9 : 0;
    const total = subtotal + delivery + fee;

    subtotalNode.textContent = formatPrice(subtotal);
    deliveryNode.textContent = formatPrice(delivery);
    feeNode.textContent = formatPrice(fee);
    totalNode.textContent = formatPrice(total);

    if (cart.length === 0) {
      itemsWrap.innerHTML = '';
      if (emptyNode) {
        emptyNode.hidden = false;
      }
      if (form) {
        form.querySelectorAll('input, button').forEach((node) => {
          node.disabled = true;
        });
      }
      return;
    }

    if (emptyNode) {
      emptyNode.hidden = true;
    }

    itemsWrap.innerHTML = cart.map((item) => `
      <article class="foovia-checkout-item">
        <img src="${item.image}" alt="${item.name}">
        <div>
          <h3>${item.name}</h3>
          <p>${item.quantity} x ${formatPrice(item.price)}</p>
          <small>${item.storeName}</small>
        </div>
        <strong>${formatPrice(Number(item.price || 0) * Number(item.quantity || 0))}</strong>
      </article>
    `).join('');
  };

  const updatePreview = () => {
    const holder = (holderField?.value || '').trim();
    const rawDigits = (numberField?.value || '').replace(/\D/g, '').slice(0, 16);
    const grouped = rawDigits.replace(/(.{4})/g, '$1 ').trim();
    const expiry = expiryField?.value || '';

    if (previewName) {
      previewName.textContent = holder || 'Foovia Customer';
    }
    if (previewNumber) {
      previewNumber.textContent = grouped || '•••• •••• •••• ••••';
    }
    if (previewExpiry) {
      previewExpiry.textContent = expiry || 'MM/YY';
    }
    if (previewBrand) {
      previewBrand.textContent = detectCardBrand(rawDigits);
    }
  };

  const normalizeFields = () => {
    if (numberField) {
      numberField.value = numberField.value.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
    }

    if (expiryField) {
      const digits = expiryField.value.replace(/\D/g, '').slice(0, 4);
      expiryField.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
    }

    if (cvvField) {
      cvvField.value = cvvField.value.replace(/\D/g, '').slice(0, 4);
    }

    if (phoneField) {
      phoneField.value = phoneField.value.replace(/[^\d+\s]/g, '').slice(0, 16);
    }
  };

  const validate = () => {
    clearErrors();
    normalizeFields();
    updatePreview();

    let valid = true;
    const holder = (holderField?.value || '').trim();
    const email = (emailField?.value || '').trim();
    const cardNumber = (numberField?.value || '').replace(/\s/g, '');
    const expiry = (expiryField?.value || '').trim();
    const cvv = (cvvField?.value || '').trim();
    const phone = (phoneField?.value || '').trim();
    const address = (addressField?.value || '').trim();
    const city = (cityField?.value || '').trim();
    const postal = (postalField?.value || '').trim();
    const country = (countryField?.value || '').trim();

    if (holder.length < 4) {
      setError('holder_name', 'Enter the full cardholder name.');
      valid = false;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setError('email', 'Enter a valid email address.');
      valid = false;
    }

    if (!/^\d{16}$/.test(cardNumber)) {
      setError('card_number', 'Card number must contain 16 digits.');
      valid = false;
    }

    if (!/^\d{2}\/\d{2}$/.test(expiry)) {
      setError('expiry', 'Expiry must be in MM/YY format.');
      valid = false;
    } else {
      const [monthText, yearText] = expiry.split('/');
      const month = Number(monthText);
      const year = Number(`20${yearText}`);
      const now = new Date();
      const expiryDate = new Date(year, month, 0);
      if (month < 1 || month > 12 || expiryDate < now) {
        setError('expiry', 'Enter a valid future expiry date.');
        valid = false;
      }
    }

    if (!/^\d{3,4}$/.test(cvv)) {
      setError('cvv', 'CVV must be 3 or 4 digits.');
      valid = false;
    }

    if (phone.replace(/\D/g, '').length < 8) {
      setError('phone', 'Enter a valid phone number.');
      valid = false;
    }

    if (address.length < 8) {
      setError('address', 'Enter a complete billing address.');
      valid = false;
    }

    if (city.length < 2) {
      setError('city', 'Enter your city.');
      valid = false;
    }

    if (!/^\d{4,6}$/.test(postal)) {
      setError('postal_code', 'Postal code must be 4 to 6 digits.');
      valid = false;
    }

    if (country.length < 3) {
      setError('country', 'Enter your country.');
      valid = false;
    }

    return valid;
  };

  const completePayment = () => {
    if (processingModal) {
      processingModal.hidden = true;
    }

    localStorage.removeItem(cartStorageKey);
    if (successReference) {
      successReference.textContent = `FV-${Date.now().toString().slice(-6)}`;
    }
    if (successModal) {
      successModal.hidden = false;
    }
  };

  [holderField, emailField, numberField, expiryField, cvvField, phoneField, addressField, cityField, postalField, countryField]
    .filter(Boolean)
    .forEach((field) => {
      field.addEventListener('input', () => {
        normalizeFields();
        updatePreview();
      });
    });

  if (form) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (!validate()) {
        return;
      }

      if (processingModal) {
        processingModal.hidden = false;
      }

      window.setTimeout(completePayment, 1900);
    });
  }

  renderSummary();
  updatePreview();
});
