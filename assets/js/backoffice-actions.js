document.addEventListener('DOMContentLoaded', () => {
  const deleteForms = document.querySelectorAll('[data-delete-product-form]');

  deleteForms.forEach((form) => {
    form.addEventListener('submit', (event) => {
      const productName = form.dataset.productName || 'this product';
      const confirmed = window.confirm(`Delete ${productName} from the marketplace?`);

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
});
