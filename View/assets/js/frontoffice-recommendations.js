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

  const buildPromotedCard = (item) => {
    const {
      productName = '',
      productPrice = '',
      productDescription = '',
      productCategory = '',
      productStock = '',
      productStores = '',
      productImage = '',
    } = item.dataset;

    return `
      <article class="foovia-promoted-detail">
        <div class="foovia-promoted-copy">
          <span class="foovia-promoted-chip">${productCategory}</span>
          <h3>${productName}</h3>
          <p>${productDescription}</p>
          <div class="foovia-promoted-meta">
            <strong>${productPrice} TND</strong>
            <span>${productStock} in stock</span>
            <span>${productStores}</span>
          </div>
          <div class="foovia-promoted-actions">
            <a href="${item.href}" class="foovia-spotlight-btn">View product</a>
            <span class="foovia-promoted-hint">Press the same quick pick again to open it directly.</span>
          </div>
        </div>
        <a href="${item.href}" class="foovia-promoted-media" aria-label="${productName}">
          <img src="${productImage}" alt="${productName}">
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
    }

    if (emptyState) {
      emptyState.hidden = true;
    }

    promoteSlot.innerHTML = buildPromotedCard(item);
    item.classList.add('is-promoted');
    promotedItem = item;
    promoteSlot.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };

  if (promoteSlot && recommendItems.length) {
    recommendItems.forEach((item) => {
      item.addEventListener('click', (event) => {
        if (item === promotedItem) {
          return;
        }

        event.preventDefault();
        promoteItem(item);
      });
    });
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
