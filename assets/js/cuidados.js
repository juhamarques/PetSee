document.addEventListener('DOMContentLoaded', function() {
  const filterToggleButton = document.getElementById('filter-toggle-btn');
  const filterPanel = document.getElementById('filter-panel');
  const infoCardsContainer = document.getElementById('info-cards-container');
  const infoCardItems = document.querySelectorAll('.info-card-item');
  const applyFiltersBtn = document.getElementById('apply-filters-btn');
  const clearFiltersBtn = document.getElementById('clear-filters-btn');
  const filterCheckboxes = document.querySelectorAll('#filter-panel input[type="checkbox"]');

  filterToggleButton.addEventListener('click', function() {
    filterPanel.style.display = filterPanel.style.display === 'flex' ? 'none' : 'flex';
  });

  document.addEventListener('click', function(event) {
    if (!filterPanel.contains(event.target) && !filterToggleButton.contains(event.target)) {
      filterPanel.style.display = 'none';
    }
  });

 
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
    filterPanel.style.display = 'none';
  });

  clearFiltersBtn.addEventListener('click', function() {
    filterCheckboxes.forEach(checkbox => {
      checkbox.checked = false; 
    });
    applyFilters(); 
    filterPanel.style.display = 'none';
  });
  
  applyFilters();
});