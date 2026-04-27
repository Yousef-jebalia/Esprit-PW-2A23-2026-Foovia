document.addEventListener('DOMContentLoaded', () => {
  const cartStorageKey = 'fooviaCartItems';
  const loadCart = () => {
    try {
      const savedCart = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
      return Array.isArray(savedCart) ? savedCart : [];
    } catch (error) {
      return [];
    }
  };
  const cart = loadCart();
  const cartCount = document.querySelector('[data-cart-count]');
  const cartModal = document.querySelector('[data-cart-modal]');
  const cartItems = document.querySelector('[data-cart-items]');
  const cartTotal = document.querySelector('[data-cart-total]');
  const picker = document.querySelector('[data-cart-picker]');
  const pickerStore = document.querySelector('[data-picker-store]');
  const pickerQuantity = document.querySelector('[data-picker-quantity]');
  const pickerName = document.querySelector('[data-picker-product-name]');
  const pickerPrice = document.querySelector('[data-picker-price]');
  const pickerReservationTotal = document.querySelector('[data-picker-reservation-total]');
  const floatingCartButtons = Array.from(document.querySelectorAll('[data-cart-toggle]'));
  let dragPreview = null;
  let pendingProduct = null;

  const parseQuantity = (value) => {
    const quantity = Number.parseInt(String(value || '1'), 10);
    return Number.isFinite(quantity) && quantity > 0 ? quantity : 1;
  };

  const formatPrice = (value) => {
    const price = Number(value || 0);
    return price.toFixed(3).replace(/\.?0+$/, '');
  };

  const saveCart = () => {
    localStorage.setItem(cartStorageKey, JSON.stringify(cart));
  };

  const setCartDragState = (isActive) => {
    floatingCartButtons.forEach((button) => {
      button.classList.toggle('is-drag-target', isActive);
    });
  };

  const cleanupDragPreview = () => {
    if (dragPreview && dragPreview.parentElement) {
      dragPreview.remove();
    }
    dragPreview = null;
  };

  const buildDragPreview = (product) => {
    cleanupDragPreview();

    const preview = document.createElement('div');
    preview.className = 'foovia-drag-preview';
    preview.innerHTML = `
      <img src="${product.image}" alt="${product.name}">
      <div class="foovia-drag-preview-copy">
        <strong>${product.name}</strong>
        <span>${formatPrice(product.price)} TND</span>
      </div>
    `;
    document.body.appendChild(preview);
    dragPreview = preview;
    return preview;
  };

  const showCheckoutMessage = (targetButton, message) => {
    const oldMessage = cartModal?.querySelector('.foovia-checkout-message');
    if (oldMessage) {
      oldMessage.remove();
    }

    if (!targetButton) {
      return;
    }

    const messageNode = document.createElement('div');
    messageNode.className = 'foovia-checkout-message';
    messageNode.textContent = message;
    targetButton.insertAdjacentElement('beforebegin', messageNode);
  };

  const renderCart = () => {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);

    if (cartCount) cartCount.textContent = String(totalItems);
    if (cartTotal) cartTotal.textContent = `${formatPrice(totalPrice)} TND`;
    if (!cartItems) return;

    if (cart.length === 0) {
      cartItems.innerHTML = '<p class="text-muted mb-0">Your cart is empty.</p>';
      return;
    }

    cartItems.innerHTML = cart.map((item, index) => `
      <article class="foovia-cart-item">
        <img src="${item.image}" alt="${item.name}">
        <div>
          <h3>${item.name}</h3>
          <p>${item.quantity} x ${formatPrice(item.price)} TND</p>
          <div class="foovia-cart-store">
            <div class="foovia-cart-store-line">
              <img src="${item.storeImage || item.image}" alt="${item.storeName}">
              <span>${item.storeName}</span>
            </div>
            <button type="button" class="foovia-cart-remove" data-cart-remove="${index}">Remove</button>
          </div>
        </div>
        <strong>${formatPrice(item.quantity * item.price)} TND</strong>
      </article>
    `).join('');

    cartItems.querySelectorAll('[data-cart-remove]').forEach((button) => {
      button.addEventListener('click', () => {
        const index = Number(button.dataset.cartRemove);
        if (!Number.isInteger(index)) return;
        cart.splice(index, 1);
        saveCart();
        renderCart();
      });
    });
  };

  const addItem = (item) => {
    const existing = cart.find((cartItem) => cartItem.id === item.id && cartItem.storeId === item.storeId);
    if (existing) {
      existing.quantity += item.quantity;
    } else {
      cart.push(item);
    }
    saveCart();
    renderCart();
  };

  const openPickerForProduct = (product) => {
    pendingProduct = product;

    if (pickerName) pickerName.textContent = pendingProduct.name;
    if (pickerPrice) pickerPrice.textContent = `${formatPrice(pendingProduct.price)} TND`;
    if (pickerStore) {
      pickerStore.innerHTML = (pendingProduct.stores || []).map((store, index) => `
        <label class="foovia-store-choice">
          <input
            type="radio"
            name="picker_store"
            value="${store.id}"
            data-store-name="${store.name}"
            data-store-image="${store.image}"
            ${index === 0 ? 'checked' : ''}
          >
          <span>${store.name}</span>
        </label>
      `).join('');
    }
    if (pickerQuantity) pickerQuantity.value = '1';
    if (pickerReservationTotal) pickerReservationTotal.textContent = '0 reservations';
    if (picker) picker.hidden = false;
  };

  const readProductFromDataset = (source) => {
    const stores = JSON.parse(source.dataset.productStores || '[]');
    return {
      id: Number(source.dataset.productId),
      name: source.dataset.productName || 'Product',
      price: Number(source.dataset.productPrice || 0),
      image: source.dataset.productImage || '',
      stores
    };
  };

  const getSelectedDetailStore = () => {
    const checkedStore = document.querySelector('[name="detail_store"]:checked');
    if (checkedStore) {
      return {
        id: Number(checkedStore.value || 0),
        name: checkedStore.dataset.storeName || checkedStore.parentElement?.textContent?.trim() || 'Store',
        image: checkedStore.dataset.storeImage || ''
      };
    }

    const storeSelect = document.querySelector('[data-detail-store]');
    const selectedStore = storeSelect?.selectedOptions[0];
    return {
      id: Number(storeSelect?.value || 0),
      name: selectedStore?.dataset.storeName || selectedStore?.textContent || 'Store',
      image: selectedStore?.dataset.storeImage || ''
    };
  };

  document.querySelectorAll('[data-cart-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      renderCart();
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-cart-close]').forEach((button) => {
    button.addEventListener('click', () => {
      if (cartModal) cartModal.hidden = true;
    });
  });

  document.querySelectorAll('[data-cart-checkout]').forEach((button) => {
    button.addEventListener('click', () => {
      const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
      const totalPrice = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);
      if (totalItems === 0) {
        showCheckoutMessage(button, 'Your cart is empty. Add a product before checkout.');
        return;
      }

      showCheckoutMessage(button, `Opening checkout for ${totalItems} item(s) worth ${formatPrice(totalPrice)} TND...`);
      window.setTimeout(() => {
        window.location.href = 'checkout.php';
      }, 220);
    });
  });

  document.querySelectorAll('[data-open-cart-picker]').forEach((button) => {
    button.addEventListener('click', () => {
      openPickerForProduct(readProductFromDataset(button));
    });
  });

  document.querySelectorAll('[data-drag-product]').forEach((card) => {
    card.addEventListener('dragstart', (event) => {
      const product = readProductFromDataset(card);
      const preview = buildDragPreview(product);
      event.dataTransfer?.setData('application/json', JSON.stringify(product));
      event.dataTransfer?.setData('text/plain', String(product.id));
      event.dataTransfer.effectAllowed = 'copy';
      if (event.dataTransfer && preview) {
        event.dataTransfer.setDragImage(preview, 36, 24);
      }
      card.classList.add('is-dragging');
      setCartDragState(true);
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      setCartDragState(false);
      cleanupDragPreview();
    });
  });

  floatingCartButtons.forEach((button) => {
    button.addEventListener('dragover', (event) => {
      event.preventDefault();
      event.dataTransfer.dropEffect = 'copy';
      setCartDragState(true);
    });

    button.addEventListener('dragenter', (event) => {
      event.preventDefault();
      setCartDragState(true);
    });

    button.addEventListener('dragleave', (event) => {
      if (event.relatedTarget && button.contains(event.relatedTarget)) {
        return;
      }
      setCartDragState(false);
    });

    button.addEventListener('drop', (event) => {
      event.preventDefault();
      setCartDragState(false);
      const payload = event.dataTransfer?.getData('application/json');
      if (!payload) {
        return;
      }

      try {
        const product = JSON.parse(payload);
        if (!product || !Array.isArray(product.stores)) {
          return;
        }
        openPickerForProduct(product);
      } catch (error) {
        // Ignore malformed drag payloads.
      }
    });
  });

  document.querySelectorAll('[data-picker-close]').forEach((button) => {
    button.addEventListener('click', () => {
      if (picker) picker.hidden = true;
    });
  });

  document.querySelectorAll('[data-picker-confirm]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!pendingProduct || !pickerStore) return;
      const selectedStore = pickerStore.querySelector('[name="picker_store"]:checked');
      addItem({
        id: pendingProduct.id,
        name: pendingProduct.name,
        price: pendingProduct.price,
        image: pendingProduct.image,
        quantity: parseQuantity(pickerQuantity?.value),
        storeId: Number(selectedStore?.value || 0),
        storeName: selectedStore?.dataset.storeName || selectedStore?.parentElement?.textContent?.trim() || 'Store',
        storeImage: selectedStore?.dataset.storeImage || ''
      });
      if (picker) picker.hidden = true;
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-picker-reserve]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!pendingProduct || !pickerStore) return;
      const selectedStore = pickerStore.querySelector('[name="picker_store"]:checked');
      const quantity = parseQuantity(pickerQuantity?.value);
      const formData = new FormData();
      formData.append('id_march', String(pendingProduct.id));
      formData.append('id_mag', selectedStore?.value || '0');
      formData.append('quantity_reservation', String(quantity));

      try {
        const response = await fetch(window.FOOVIA_RESERVATION_ENDPOINT || '../../../Controller/Marchandise_Controller.php?action=reserve', {
          method: 'POST',
          body: formData
        });
        if (!response.ok) throw new Error('Reservation failed');
        showReserveBubble(button, 'Reservation complete.');
        if (pickerReservationTotal) {
          const current = Number.parseInt(pickerReservationTotal.textContent, 10) || 0;
          pickerReservationTotal.textContent = `${current + quantity} reservations`;
        }
      } catch (error) {
        showReserveBubble(button, 'Reservation could not be saved.');
      }
    });
  });

  document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
    button.addEventListener('click', () => {
      const quantityInput = document.querySelector('[data-detail-quantity]');
      const selectedStore = getSelectedDetailStore();

      addItem({
        id: Number(button.dataset.productId),
        name: button.dataset.productName || 'Product',
        price: Number(button.dataset.productPrice || 0),
        image: button.dataset.productImage || '',
        quantity: parseQuantity(quantityInput?.value),
        storeId: selectedStore.id,
        storeName: selectedStore.name,
        storeImage: selectedStore.image
      });
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-reserve-product]').forEach((button) => {
    button.addEventListener('click', async () => {
      const quantityInput = document.querySelector('[data-detail-quantity]');
      const selectedStore = getSelectedDetailStore();
      const formData = new FormData();
      formData.append('id_march', button.dataset.productId || '0');
      formData.append('id_mag', String(selectedStore.id));
      formData.append('quantity_reservation', String(parseQuantity(quantityInput?.value)));

      try {
        const response = await fetch(window.FOOVIA_RESERVATION_ENDPOINT || '../../../Controller/Marchandise_Controller.php?action=reserve', {
          method: 'POST',
          body: formData
        });
        if (!response.ok) throw new Error('Reservation failed');
        showReserveBubble(button, 'Reservation complete.');
        updateReservationTotal(parseQuantity(quantityInput?.value));
      } catch (error) {
        showReserveBubble(button, 'Reservation could not be saved.');
      }
    });
  });

  const showReserveBubble = (target, message) => {
    const existing = target.parentElement.querySelector('.foovia-reserve-bubble');
    if (existing) existing.remove();
    const bubble = document.createElement('span');
    bubble.className = 'foovia-reserve-bubble';
    bubble.textContent = message;
    target.insertAdjacentElement('afterend', bubble);
    window.setTimeout(() => bubble.remove(), 2600);
  };

  const updateReservationTotal = (quantity) => {
    const totalNode = document.querySelector('[data-reservation-total]');
    if (!totalNode) return;
    const current = Number.parseInt(totalNode.textContent, 10) || 0;
    totalNode.textContent = `${current + quantity} reservations`;
  };

  renderCart();
});
