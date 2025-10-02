document.addEventListener('DOMContentLoaded', function() {
  // Suporte ao novo filtro horizontal
  const filterPanel = document.getElementById('filter-panel-horizontal') || document.getElementById('filter-panel');
  const infoCardsContainer = document.getElementById('info-cards-container');
  const infoCardItems = document.querySelectorAll('.info-card-item');
  const applyFiltersBtn = document.getElementById('apply-filters-btn');
  const clearFiltersBtn = document.getElementById('clear-filters-btn');
  // Seleciona checkboxes do filtro horizontal ou antigo
  const filterCheckboxes = filterPanel ? filterPanel.querySelectorAll('input[type="checkbox"]') : document.querySelectorAll('input[type="checkbox"]');
 
  function applyFilters() {
    const selectedCategories = Array.from(filterCheckboxes)
      .filter(checkbox => checkbox.checked)
      .map(checkbox => checkbox.value);

    if (selectedCategories.length === 0) {
      infoCardItems.forEach(card => {
          card.classList.remove('hidden');
      });
    } else {
      infoCardItems.forEach(card => {
        const cardCategory = card.dataset.category;
        if (selectedCategories.includes(cardCategory)) {
          card.classList.remove('hidden');
        } else {
          card.classList.add('hidden');
        }
      });
    }
  }

  applyFiltersBtn.addEventListener('click', function() {
    applyFilters();
  });

  clearFiltersBtn.addEventListener('click', function() {
    filterCheckboxes.forEach(checkbox => {
      checkbox.checked = false; 
    })
    applyFilters(); 
  });
  
  applyFilters();
});