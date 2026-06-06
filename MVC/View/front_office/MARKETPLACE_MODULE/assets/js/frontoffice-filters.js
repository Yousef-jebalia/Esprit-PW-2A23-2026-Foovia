document.addEventListener('DOMContentLoaded', () => {
  const cards = Array.from(document.querySelectorAll('[data-product-card]'));
  const grid = document.querySelector('[data-market-grid]');
  const searchInput = document.querySelector('[data-product-search]');
  const storeSelect = document.querySelector('[data-store-filter]');
  const categorySelect = document.querySelector('[data-category-filter]');
  const sortSelect = document.querySelector('[data-product-sort]');
  const clearButton = document.querySelector('[data-clear-filters]');
  const resultCount = document.querySelector('[data-result-count]');
  const activeFilter = document.querySelector('[data-active-filter]');
  const shortcutButtons = Array.from(document.querySelectorAll('[data-catalog-shortcut]'));
  const emptyState = document.querySelector('[data-empty-state]');
  const pagination = document.querySelector('[data-market-pagination]');
  const pageButtons = pagination ? Array.from(pagination.querySelectorAll('[data-market-page]')) : [];
  const prevPageButton = pagination?.querySelector('[data-market-prev]');
  const nextPageButton = pagination?.querySelector('[data-market-next]');
  const pageStatus = pagination?.querySelector('[data-market-page-status]');
  const pageSize = Number(pagination?.dataset.pageSize || 8);
  let currentPage = 1;

  if (!cards.length || !grid || !searchInput || !storeSelect || !categorySelect || !sortSelect) return;

  const text = (card, key) => (card.dataset[key] || '').toLowerCase();
  const number = (card, key) => Number.parseFloat(card.dataset[key] || '0') || 0;
  const dateValue = (card) => Date.parse(card.dataset.productExpiry || '') || 0;

  const scrollToCatalog = () => {
    const catalog = document.querySelector('#products');
    if (!catalog) return;
    const top = catalog.getBoundingClientRect().top + window.scrollY - 88;
    window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
  };

  const getMatchingCards = () => cards.filter((card) => card.dataset.filterMatch !== '0');

  const sortCards = () => {
    const sort = sortSelect.value;
    const sorted = [...cards].sort((left, right) => {
      if (sort === 'price-asc') return number(left, 'productPrice') - number(right, 'productPrice');
      if (sort === 'price-desc') return number(right, 'productPrice') - number(left, 'productPrice');
      if (sort === 'stock-desc') return number(right, 'productStock') - number(left, 'productStock');
      if (sort === 'fresh-desc') return dateValue(right) - dateValue(left);
      if (sort === 'name-asc') return text(left, 'productName').localeCompare(text(right, 'productName'));
      return number(left, 'originalIndex') - number(right, 'originalIndex');
    });

    sorted.forEach((card) => grid.appendChild(card));
  };

  const updateSummary = (matchingCards) => {
    const total = matchingCards.length;
    if (resultCount) resultCount.textContent = `${total} ${total === 1 ? 'product' : 'products'}`;

    const labels = [];
    if (searchInput.value.trim()) labels.push(`matching "${searchInput.value.trim()}"`);
    if (categorySelect.value) labels.push(`in ${categorySelect.options[categorySelect.selectedIndex].text}`);
    if (storeSelect.value) labels.push(`from ${storeSelect.options[storeSelect.selectedIndex].text}`);
    if (activeFilter) {
      activeFilter.textContent = labels.length
        ? `Showing ${total} ${total === 1 ? 'product' : 'products'} ${labels.join(' ')}`
        : `Showing all ${total} available products`;
    }

    if (clearButton) clearButton.disabled = labels.length === 0 && sortSelect.value === 'default';
  };

  const renderPage = (shouldScroll = false) => {
    const matchingCards = getMatchingCards();
    const totalPages = Math.max(1, Math.ceil(matchingCards.length / pageSize));
    currentPage = Math.min(Math.max(currentPage, 1), totalPages);
    const start = (currentPage - 1) * pageSize;
    const end = start + pageSize;
    const currentCards = new Set(matchingCards.slice(start, end));

    cards.forEach((card) => {
      card.style.display = currentCards.has(card) ? '' : 'none';
    });

    if (emptyState) emptyState.style.display = matchingCards.length === 0 ? '' : 'none';
    if (pagination) pagination.hidden = matchingCards.length <= pageSize;

    if (pageStatus) {
      const visibleStart = matchingCards.length === 0 ? 0 : start + 1;
      const visibleEnd = Math.min(end, matchingCards.length);
      pageStatus.textContent = `${visibleStart}-${visibleEnd} of ${matchingCards.length} products`;
    }

    if (prevPageButton) prevPageButton.disabled = currentPage <= 1;
    if (nextPageButton) nextPageButton.disabled = currentPage >= totalPages;

    pageButtons.forEach((button) => {
      const page = Number(button.dataset.marketPage);
      button.hidden = page > totalPages;
      button.classList.toggle('is-active', page === currentPage);
      button.setAttribute('aria-current', page === currentPage ? 'page' : 'false');
    });

    updateSummary(matchingCards);
    if (shouldScroll) scrollToCatalog();
  };

  const applyFilters = (shouldScroll = false) => {
    const search = searchInput.value.trim().toLowerCase();
    const selectedStore = storeSelect.value.trim().toLowerCase();
    const selectedCategory = categorySelect.value.trim().toLowerCase();

    cards.forEach((card) => {
      const searchable = [
        text(card, 'productName'),
        text(card, 'productDescription'),
        text(card, 'storeName'),
        text(card, 'productCategory'),
      ].join(' ');
      const visible = (!search || searchable.includes(search))
        && (!selectedStore || text(card, 'storeName').includes(selectedStore))
        && (!selectedCategory || text(card, 'productCategory').includes(selectedCategory));

      card.dataset.filterMatch = visible ? '1' : '0';
    });

    sortCards();
    currentPage = 1;
    renderPage(shouldScroll);
  };

  searchInput.addEventListener('input', () => applyFilters(false));
  storeSelect.addEventListener('change', () => applyFilters(false));
  categorySelect.addEventListener('change', () => applyFilters(false));
  sortSelect.addEventListener('change', () => applyFilters(false));

  clearButton?.addEventListener('click', () => {
    searchInput.value = '';
    storeSelect.value = '';
    categorySelect.value = '';
    sortSelect.value = 'default';
    applyFilters(false);
    searchInput.focus();
  });

  shortcutButtons.forEach((button) => {
    button.addEventListener('click', () => {
      searchInput.value = '';
      storeSelect.value = '';
      categorySelect.value = '';
      const shortcut = button.dataset.catalogShortcut;
      sortSelect.value = shortcut === 'affordable'
        ? 'price-asc'
        : shortcut === 'stock'
          ? 'stock-desc'
          : 'fresh-desc';
      applyFilters(true);
    });
  });

  prevPageButton?.addEventListener('click', () => {
    currentPage -= 1;
    renderPage(true);
  });

  nextPageButton?.addEventListener('click', () => {
    currentPage += 1;
    renderPage(true);
  });

  pageButtons.forEach((button) => {
    button.addEventListener('click', () => {
      currentPage = Number(button.dataset.marketPage || 1);
      renderPage(true);
    });
  });

  applyFilters(false);
});
