document.addEventListener('DOMContentLoaded', () => {
  const distanceInput = document.getElementById('filter-distance');
  const distanceLabel = document.getElementById('distance-value');
  const filterForm = document.getElementById('filter-form');
  const adCards = document.querySelectorAll('.ad-card');

  function updateDistance() {
    distanceLabel.textContent = distanceInput.value;
    distanceInput.style.setProperty('--value', distanceInput.value);
  }

  updateDistance();
  distanceInput.addEventListener('input', updateDistance);

  function calcularDistancia(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  const normalize = (s) =>
    (s ?? '').toString().trim().toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  function mapStatus(s) {
    const x = normalize(s);
    if (x.includes('perd')) return 'perdido';
    if (x.includes('encontr')) return 'encontrado';
    if (x.includes('adoc')) return 'adocao';
    return '';
  }

  function mapSpecies(s) {
    const x = normalize(s);
    if (['cachorro','cao','cão','dog','canino'].some(k => x.includes(k))) return 'cachorro';
    if (['gato','cat','felino'].some(k => x.includes(k))) return 'gato';
    if (['passaro','pássaro','ave','bird'].some(k => x.includes(k))) return 'passaro';
    if (['roedor','rodent','hamster','coelho'].some(k => x.includes(k))) return 'roedor';
    return x || '';
  }

  function mapSex(s) {
    const x = normalize(s);
    if (x.startsWith('m')) return 'macho';
    if (x.startsWith('f')) return 'femea';
    return x || '';
  }

  function parseCoordRaw(raw) {
    // Trata null, undefined, '' como NaN (não usa distância)
    if (raw === null || raw === undefined) return NaN;
    const str = String(raw).trim();
    if (str === '') return NaN;
    const n = Number(str);
    return Number.isFinite(n) ? n : NaN;
  }

  function canUseDistance(max, userLat, userLon, adLat, adLon) {
    return Number.isFinite(max) && max > 0 &&
           Number.isFinite(userLat) && Number.isFinite(userLon) &&
           Number.isFinite(adLat) && Number.isFinite(adLon);
  }

  filterForm.addEventListener('submit', (event) => {
    event.preventDefault();

    const statusInput = document.querySelector('input[name="status"]:checked');
    const typeInput   = document.querySelector('input[name="type"]:checked');
    const sexInput    = document.querySelector('input[name="sex"]:checked');

    const filterStatus = statusInput ? mapStatus(statusInput.value) : '';
    const filterType   = typeInput   ? mapSpecies(typeInput.value)  : '';
    const filterSex    = sexInput    ? mapSex(sexInput.value)       : '';
    const filterName   = normalize(document.getElementById('filter-name').value);

    const maxDistanceRaw = Number(distanceInput.value);
    const effectiveMax   = Number.isFinite(maxDistanceRaw) && maxDistanceRaw > 0 ? maxDistanceRaw : Infinity;

    // PEGA USER COORDS SEM CONVERTER null/'' PARA 0
    const userLat = parseCoordRaw(window.USER_LAT);
    const userLon = parseCoordRaw(window.USER_LON);

    adCards.forEach(card => {
      // Status
      const statusEl = card.querySelector('.ad-status');
      const adStatus = mapStatus(statusEl?.dataset?.status ?? statusEl?.textContent ?? '');

      // Nome
      const adName = normalize(card.querySelector('.ad-name')?.textContent ?? '');

      // Espécie • Sexo
      const speciesSexEl = card.querySelector('.ad-species');
      let adSpecies = '', adSex = '';
      if (speciesSexEl) {
        const raw = speciesSexEl.textContent || '';
        const parts = raw.includes('•') ? raw.split('•') : raw.split(/[-|]/);
        adSpecies = mapSpecies(parts[0] ?? '');
        adSex     = mapSex(parts[1] ?? '');
      }

      // Coordenadas DO CARD — NÃO CONVERTER '' PARA 0
      const adLat = parseCoordRaw(card.getAttribute('data-lat'));
      const adLon = parseCoordRaw(card.getAttribute('data-lon'));

      let showCard = true;

      if (filterStatus && filterStatus !== adStatus) showCard = false;
      if (showCard && filterName && !adName.includes(filterName)) showCard = false;
      if (showCard && filterType && filterType !== adSpecies) showCard = false;
      if (showCard && filterSex && filterSex !== adSex) showCard = false;

      if (showCard && canUseDistance(effectiveMax, userLat, userLon, adLat, adLon)) {
        const distancia = calcularDistancia(userLat, userLon, adLat, adLon);
        if (distancia > effectiveMax) showCard = false;
      }
      // Se não puder usar distância (coords inválidas), NÃO filtra por distância.

      const container = card.closest('[data-ad-container]') || card.closest('.col-md-6') || card;
      container.style.display = showCard ? '' : 'none';
    });
  });
});