document.addEventListener('DOMContentLoaded', () => {
  const cards = Array.from(document.querySelectorAll('[data-product-card]'));
  const searchInput = document.querySelector('[data-product-search]');
  const storeSelect = document.querySelector('[data-store-filter]');
  const emptyState = document.querySelector('[data-empty-state]');
  const pagination = document.querySelector('[data-market-pagination]');
  const pageButtons = pagination ? Array.from(pagination.querySelectorAll('[data-market-page]')) : [];
  const prevPageButton = pagination?.querySelector('[data-market-prev]');
  const nextPageButton = pagination?.querySelector('[data-market-next]');
  const pageStatus = pagination?.querySelector('[data-market-page-status]');
  const pageSize = Number(pagination?.dataset.pageSize || 8);
  let currentPage = 1;

  if (!cards.length || !searchInput || !storeSelect) return;

  const scrollToCatalog = () => {
    const catalog = document.querySelector('#products');
    if (!catalog) return;
    const top = catalog.getBoundingClientRect().top + window.scrollY - 88;
    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
  };

  const getMatchingCards = () => cards.filter((card) => card.dataset.filterMatch !== '0');

  const renderPage = (shouldScroll = false) => {
    const matchingCards = getMatchingCards();
    const totalPages = Math.max(1, Math.ceil(matchingCards.length / pageSize));
    currentPage = Math.min(Math.max(currentPage, 1), totalPages);
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;

    cards.forEach((card) => {
      const isMatch = card.dataset.filterMatch !== '0';
      const isOnPage = matchingCards.slice(start, end).includes(card);
      card.style.display = isMatch && isOnPage ? '' : 'none';
    });

    if (emptyState) emptyState.style.display = matchingCards.length === 0 ? '' : 'none';

    if (pagination) {
      pagination.hidden = matchingCards.length <= pageSize;
    }

    if (pageStatus) {
      const visibleStart = matchingCards.length === 0 ? 0 : start + 1;
      const visibleEnd = Math.min(end, matchingCards.length);
      pageStatus.textContent = `${visibleStart}-${visibleEnd} of ${matchingCards.length} foods`;
    }

    if (prevPageButton) {
      prevPageButton.disabled = currentPage <= 1;
    }

    if (nextPageButton) {
      nextPageButton.disabled = currentPage >= totalPages;
    }

    pageButtons.forEach((button) => {
      const page = Number(button.dataset.marketPage);
      const isAvailable = page <= totalPages;
      button.hidden = !isAvailable;
      button.classList.toggle('is-active', page === currentPage);
      button.setAttribute('aria-current', page === currentPage ? 'page' : 'false');
    });

    if (shouldScroll) scrollToCatalog();
  };

  const applyFilters = () => {
    const search = searchInput.value.trim().toLowerCase();
    const selectedStore = storeSelect.value.trim().toLowerCase();

    cards.forEach((card) => {
      const productName = (card.dataset.productName || '').toLowerCase();
      const storeName = (card.dataset.storeName || '').toLowerCase();
      const description = (card.dataset.productDescription || '').toLowerCase();

      const searchMatch = !search || productName.includes(search) || description.includes(search) || storeName.includes(search);
      const storeMatch = !selectedStore || storeName.includes(selectedStore);
      const visible = searchMatch && storeMatch;

      card.dataset.filterMatch = visible ? '1' : '0';
    });

    currentPage = 1;
    renderPage(false);
  };

  searchInput.addEventListener('input', applyFilters);
  storeSelect.addEventListener('change', applyFilters);

  if (prevPageButton) {
    prevPageButton.addEventListener('click', () => {
      currentPage -= 1;
      renderPage(true);
    });
  }

  if (nextPageButton) {
    nextPageButton.addEventListener('click', () => {
      currentPage += 1;
      renderPage(true);
    });
  }

  pageButtons.forEach((button) => {
    button.addEventListener('click', () => {
      currentPage = Number(button.dataset.marketPage || 1);
      renderPage(true);
    });
  });

  applyFilters();
});
