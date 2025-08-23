const distanceInput = document.getElementById('filter-distance');
const distanceLabel = document.getElementById('distance-value');

function updateDistance() {
  distanceLabel.textContent = distanceInput.value;
  distanceInput.style.setProperty('--value', distanceInput.value);
}

distanceInput.addEventListener('input', updateDistance);
updateDistance();

/*---------------------------
Lógica de Filtragem da Página Principal
----------------------------*/

const filterForm = document.getElementById('filter-form');
const adCards = document.querySelectorAll('.ad-card');

filterForm.addEventListener('submit', (event) => {
    event.preventDefault(); // Impede o envio do formulário

    // 1. Obter os valores dos filtros
    const filterStatus = document.querySelector('input[name="status"]:checked').value;
    const filterName = document.getElementById('filter-name').value.toLowerCase();
    const filterType = document.querySelector('input[name="type"]:checked').value;
    const filterSex = document.querySelector('input[name="sex"]:checked').value;

    // 2. Iterar sobre todos os anúncios e aplicar a lógica de visibilidade
    adCards.forEach(card => {
        const adStatus = card.querySelector('.ad-status').textContent.trim().toLowerCase();
        const adName = card.querySelector('.ad-name').textContent.trim().toLowerCase();
        const adSpecies = card.querySelector('.ad-species').textContent.trim().toLowerCase().split('•')[0].trim();
        const adSex = card.querySelector('.ad-species').textContent.trim().toLowerCase().split('•')[1].trim();
        
        // Mapeamento dos textos do status para os valores do filtro
        const statusMap = {
            'perdido': 'desaparecido',
            'para adoção': 'adocao',
            'encontrado': 'encontrado'
        };
        const mappedStatus = statusMap[adStatus] || '';

        let showCard = true;

        // Lógica para filtrar por status
        if (filterStatus && filterStatus !== mappedStatus) {
            showCard = false;
        }

        // Lógica para filtrar por nome
        if (filterName && !adName.includes(filterName)) {
            showCard = false;
        }
        
        // Lógica para filtrar por tipo do animal (espécie)
        if (filterType && filterType !== adSpecies) {
            showCard = false;
        }

        // Lógica para filtrar por sexo
        if (filterSex && filterSex !== adSex) {
            showCard = false;
        }
        
        // Exibe ou esconde o cartão com base nas regras
        if (showCard) {
            card.closest('.col-md-6').style.display = '';
        } else {
            card.closest('.col-md-6').style.display = 'none';
        }
    });
});