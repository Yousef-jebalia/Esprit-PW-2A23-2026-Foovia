document.addEventListener('DOMContentLoaded', () => {
  const cards = Array.from(document.querySelectorAll('[data-product-card]'));
  const searchInput = document.querySelector('[data-product-search]');
  const storeSelect = document.querySelector('[data-store-filter]');
  const emptyState = document.querySelector('[data-empty-state]');

  if (!cards.length || !searchInput || !storeSelect) return;

  const applyFilters = () => {
    const search = searchInput.value.trim().toLowerCase();
    const selectedStore = storeSelect.value.trim().toLowerCase();
    let visibleCount = 0;

    cards.forEach((card) => {
      const productName = (card.dataset.productName || '').toLowerCase();
      const storeName = (card.dataset.storeName || '').toLowerCase();
      const description = (card.dataset.productDescription || '').toLowerCase();

      const searchMatch = !search || productName.includes(search) || description.includes(search) || storeName.includes(search);
      const storeMatch = !selectedStore || storeName === selectedStore;
      const visible = searchMatch && storeMatch;

      card.style.display = visible ? '' : 'none';
      if (visible) visibleCount += 1;
    });

    if (emptyState) emptyState.style.display = visibleCount === 0 ? '' : 'none';
  };

  searchInput.addEventListener('input', applyFilters);
  storeSelect.addEventListener('change', applyFilters);
});
