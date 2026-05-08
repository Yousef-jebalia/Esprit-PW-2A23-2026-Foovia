document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const loader = document.querySelector('[data-page-loader]');
  const hideLoader = () => {
    if (!loader) {
      return;
    }

    loader.classList.add('is-hidden');
    body.classList.remove('foovia-is-loading');
    window.setTimeout(() => {
      loader.setAttribute('hidden', 'hidden');
    }, 380);
  };

  body.classList.add('foovia-is-loading');
  if (document.readyState === 'complete') {
    hideLoader();
  } else {
    window.addEventListener('load', hideLoader, { once: true });
    window.setTimeout(hideLoader, 1400);
  }

  const spotlight = document.querySelector('[data-recommend-spotlight]');
  const track = document.querySelector('[data-recommend-track]');
  const prevButton = document.querySelector('[data-recommend-prev]');
  const nextButton = document.querySelector('[data-recommend-next]');

  if (track && prevButton && nextButton) {
    const getScrollAmount = () => {
      const firstCard = track.querySelector('.foovia-recommend-card');
      if (!firstCard) {
        return 320;
      }

      return firstCard.getBoundingClientRect().width + 16;
    };

    prevButton.addEventListener('click', () => {
      track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
    });

    nextButton.addEventListener('click', () => {
      track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
    });
  }

  const promoteSlot = document.querySelector('[data-recommend-promote-slot]');
  const emptyState = document.querySelector('[data-recommend-empty]');
  const recommendItems = Array.from(document.querySelectorAll('[data-recommend-item]'));
  let promotedItem = null;
  let activeQuantity = 1;

  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const parsePrice = (value) => {
    const price = Number.parseFloat(value);
    return Number.isFinite(price) ? price : 0;
  };

  const formatPrice = (value) => {
    const price = Number(value);
    if (!Number.isFinite(price)) {
      return '0';
    }

    return price.toFixed(2).replace(/\.?0+$/, '');
  };

  const formatQuantity = (quantity, unit) => {
    const cleanQuantity = Number(quantity);
    if (unit === 'piece') {
      return `${cleanQuantity} ${cleanQuantity === 1 ? 'piece' : 'pieces'}`;
    }

    return `${formatPrice(cleanQuantity)} ${unit}`;
  };

  const getImpactProfile = ({ productName = '', productCategory = '' }) => {
    const source = `${productName} ${productCategory}`.toLowerCase();
    if (source.includes('egg')) {
      return { co2: 0.35, water: 52, meals: 0.45, tip: 'Eggs spoil quietly. Buy only what you will cook this week.' };
    }
    if (source.includes('milk') || source.includes('dairy') || source.includes('cheese') || source.includes('yogurt')) {
      return { co2: 1.2, water: 628, meals: 1, tip: 'Dairy has a bigger footprint. Keep it cold and plan portions before checkout.' };
    }
    if (source.includes('meat') || source.includes('chicken') || source.includes('beef') || source.includes('fish')) {
      return { co2: 4.8, water: 1200, meals: 2, tip: 'Protein is high impact. Smaller planned portions reduce waste fast.' };
    }
    if (source.includes('fruit') || source.includes('banana') || source.includes('apple') || source.includes('orange')) {
      return { co2: 0.42, water: 115, meals: 1, tip: 'Pick fruit by ripeness: ready now for snacks, firmer ones for later.' };
    }
    if (source.includes('vegetable') || source.includes('tomato') || source.includes('potato') || source.includes('lettuce')) {
      return { co2: 0.32, water: 86, meals: 1.2, tip: 'Vegetables are easiest to rescue when you plan two meals around them.' };
    }

    return { co2: 0.55, water: 140, meals: 1, tip: 'Start with a realistic quantity, then add more only if the week needs it.' };
  };

  const storesSummary = (storesPayload, fallback) => {
    try {
      const stores = JSON.parse(storesPayload || '[]');
      if (Array.isArray(stores) && stores.length) {
        return stores.slice(0, 2).map((store) => store.name).join(' + ');
      }
    } catch (error) {
      // Fall back to the plain label below.
    }

    return fallback || 'Selected Foovia stores';
  };

  const buildPromotedCard = (item) => {
    const {
      productName = '',
      productPrice = '',
      productDescription = '',
      productCategory = '',
      productStock = '',
      productStores = '',
      productImage = '',
      productUnit = 'kg',
    } = item.dataset;
    const profile = getImpactProfile(item.dataset);
    const total = parsePrice(productPrice) * activeQuantity;
    const co2 = profile.co2 * activeQuantity;
    const water = profile.water * activeQuantity;
    const meals = profile.meals * activeQuantity;
    const stores = storesSummary(productStores, productStores);

    return `
      <article class="foovia-promoted-detail foovia-waste-planner">
        <div class="foovia-promoted-copy">
          <span class="foovia-promoted-chip">${escapeHtml(productCategory)}</span>
          <h3>${escapeHtml(productName)}</h3>
          <p>${escapeHtml(profile.tip)}</p>
          <div class="foovia-promoted-meta">
            <strong>${formatPrice(productPrice)} TND / ${escapeHtml(productUnit)}</strong>
            <span>${escapeHtml(productStock)} in stock</span>
            <span>${escapeHtml(stores)}</span>
          </div>
          <div class="foovia-waste-controls" data-waste-controls>
            <button type="button" data-waste-step="-1" aria-label="Reduce quantity">-</button>
            <div>
              <span>Planned quantity</span>
              <strong>${formatQuantity(activeQuantity, productUnit)}</strong>
            </div>
            <button type="button" data-waste-step="1" aria-label="Increase quantity">+</button>
          </div>
          <div class="foovia-waste-impact" aria-label="Estimated food waste impact">
            <span><b>${formatPrice(total)} TND</b>Total basket</span>
            <span><b>${formatPrice(co2)} kg</b>CO2e protected</span>
            <span><b>${Math.round(water)} L</b>Water respected</span>
            <span><b>${formatPrice(meals)}</b>Meal portions</span>
          </div>
          <div class="foovia-promoted-actions">
            <button
              type="button"
              class="foovia-spotlight-btn"
              data-open-cart-picker
              data-recommend-add
              data-recommend-quantity="${activeQuantity}"
              data-product-id="${escapeHtml(item.dataset.productId || '')}"
              data-product-name="${escapeHtml(productName)}"
              data-product-price="${escapeHtml(productPrice)}"
              data-product-unit="${escapeHtml(productUnit)}"
              data-product-image="${escapeHtml(productImage)}"
              data-product-stores="${escapeHtml(productStores)}"
            >Add planned amount</button>
            <a href="${item.href}" class="foovia-spotlight-link">View product</a>
          </div>
        </div>
        <a href="${item.href}" class="foovia-promoted-media" aria-label="${escapeHtml(productName)}">
          <img src="${escapeHtml(productImage)}" alt="${escapeHtml(productName)}">
          <span class="foovia-waste-orbit foovia-waste-orbit-one">Buy less</span>
          <span class="foovia-waste-orbit foovia-waste-orbit-two">Use more</span>
        </a>
      </article>
    `;
  };

  const promoteItem = (item) => {
    if (!promoteSlot) {
      return;
    }

    if (promotedItem) {
      promotedItem.classList.remove('is-promoted');
      promotedItem.closest('.foovia-recommend-card')?.classList.remove('has-promoted-item');
    }

    if (emptyState) {
      emptyState.hidden = true;
    }

    promoteSlot.innerHTML = buildPromotedCard(item);
    item.classList.add('is-promoted');
    item.closest('.foovia-recommend-card')?.classList.add('has-promoted-item');
    promotedItem = item;
    promoteSlot.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  const updatePromotedQuantity = (step) => {
    if (!promotedItem) {
      return;
    }

    activeQuantity = Math.max(1, Math.min(12, activeQuantity + step));
    promoteSlot.innerHTML = buildPromotedCard(promotedItem);
  };

  if (promoteSlot) {
    promoteSlot.addEventListener('click', (event) => {
      const stepButton = event.target.closest('[data-waste-step]');
      if (!stepButton) {
        return;
      }

      updatePromotedQuantity(Number(stepButton.dataset.wasteStep || 0));
    });
  }

  if (promoteSlot && recommendItems.length) {
    recommendItems.forEach((item) => {
      item.addEventListener('click', (event) => {
        if (item === promotedItem) {
          return;
        }

        event.preventDefault();
        activeQuantity = 1;
        promoteItem(item);
      });
    });

    promoteItem(recommendItems[0]);
  }

  if (!spotlight) {
    return;
  }

  const slides = Array.from(spotlight.querySelectorAll('[data-spotlight-slide]'));
  const dots = Array.from(document.querySelectorAll('[data-spotlight-dot]'));

  if (slides.length <= 1 || dots.length !== slides.length) {
    return;
  }

  let activeIndex = 0;
  let intervalId = null;

  const showSlide = (index) => {
    activeIndex = index;
    slides.forEach((slide, slideIndex) => {
      slide.classList.toggle('is-active', slideIndex === index);
    });
    dots.forEach((dot, dotIndex) => {
      dot.classList.toggle('is-active', dotIndex === index);
      dot.setAttribute('aria-pressed', dotIndex === index ? 'true' : 'false');
    });
  };

  const startRotation = () => {
    stopRotation();
    intervalId = window.setInterval(() => {
      showSlide((activeIndex + 1) % slides.length);
    }, 4200);
  };

  const stopRotation = () => {
    if (intervalId !== null) {
      window.clearInterval(intervalId);
      intervalId = null;
    }
  };

  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
      showSlide(index);
      startRotation();
    });
  });

  spotlight.addEventListener('mouseenter', stopRotation);
  spotlight.addEventListener('mouseleave', startRotation);
  spotlight.addEventListener('focusin', stopRotation);
  spotlight.addEventListener('focusout', startRotation);

  showSlide(0);
  startRotation();
});
