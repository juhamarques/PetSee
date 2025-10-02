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
    if (x.includes('resgat')) return 'resgatado';
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

  const useCepCheckbox = document.getElementById('use-my-cep');
  const cepEntryDiv = document.getElementById('cep-entry');
  useCepCheckbox.addEventListener('change', () => {
    cepEntryDiv.style.display = useCepCheckbox.checked ? 'block' : 'none';
  });

  // Filtro de status agora permite múltiplos
  filterForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    // Múltiplos status
    const statusInputs = document.querySelectorAll('input[name="status"]:checked');
    const filterStatuses = Array.from(statusInputs).map(i => mapStatus(i.value));
    // Múltiplos tipos
    const typeInputs = document.querySelectorAll('input[name="type"]:checked');
    const filterTypes = Array.from(typeInputs).map(i => mapSpecies(i.value));
    // Múltiplos sexos
    const sexInputs = document.querySelectorAll('input[name="sex"]:checked');
    const filterSexes = Array.from(sexInputs).map(i => mapSex(i.value));
    const useCep      = useCepCheckbox.checked;
    const userCepInput = document.getElementById('user-cep');
    let userCep = (userCepInput && userCepInput.value.trim()) ? userCepInput.value.trim() : (window.USER_CEP || '');

    const filterName   = normalize(document.getElementById('filter-name').value);

    const maxDistanceRaw = Number(distanceInput.value);
    const effectiveMax   = Number.isFinite(maxDistanceRaw) && maxDistanceRaw > 0 ? maxDistanceRaw : Infinity;

    let userLat = parseCoordRaw(window.USER_LAT);
    let userLon = parseCoordRaw(window.USER_LON);

    if (useCep && userCep) {
      try {
        const q = encodeURIComponent(userCep + ', Brasil');
        const url = `https://nominatim.openstreetmap.org/search?q=${q}&format=json&limit=1`;
        const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
        if (resp.ok) {
          const j = await resp.json();
          if (Array.isArray(j) && j.length > 0) {
            userLat = parseCoordRaw(j[0].lat);
            userLon = parseCoordRaw(j[0].lon);
          }
        }
      } catch (e) {
        userLat = NaN; userLon = NaN;
      }
    } else {
      if (!(Number.isFinite(userLat) && Number.isFinite(userLon))) {
        try {
          const pos = await new Promise((resolve, reject) => {
            if (!navigator.geolocation) return reject(new Error('Geolocalização não suportada')); 
            navigator.geolocation.getCurrentPosition(resolve, reject, {enableHighAccuracy:false, timeout:8000});
          });
          userLat = parseCoordRaw(pos.coords.latitude);
          userLon = parseCoordRaw(pos.coords.longitude);
          window.USER_LAT = userLat; window.USER_LON = userLon;
        } catch (e) {
          userLat = NaN; userLon = NaN;
        }
      }
    }

    const geocodeCepForCard = async (card) => {
      const hasLat = parseCoordRaw(card.getAttribute('data-lat'));
      const hasLon = parseCoordRaw(card.getAttribute('data-lon'));
      if (Number.isFinite(hasLat) && Number.isFinite(hasLon)) return true;

      const desc = card.querySelector('.ad-description')?.textContent || '';
      const m = desc.match(/CEP[:\s]*([0-9]{5}-?[0-9]{3})/i);
      const cep = m ? m[1].replace(/\D/g,'') : null;
      if (!cep) return false;

      const q = encodeURIComponent(cep + ', Brasil');
      const url = `https://nominatim.openstreetmap.org/search?q=${q}&format=json&limit=1`;
      try {
        const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return false;
        const j = await resp.json();
        if (!Array.isArray(j) || j.length === 0) return false;
        const lat = parseFloat(j[0].lat);
        const lon = parseFloat(j[0].lon);
        if (!Number.isFinite(lat) || !Number.isFinite(lon)) return false;
        card.setAttribute('data-lat', String(lat));
        card.setAttribute('data-lon', String(lon));
        return true;
      } catch (err) {
        return false;
      }
    };

    const geocodePromises = [];
    adCards.forEach(card => {
      const adLat = parseCoordRaw(card.getAttribute('data-lat'));
      const adLon = parseCoordRaw(card.getAttribute('data-lon'));
      if (!(Number.isFinite(adLat) && Number.isFinite(adLon))) {
        geocodePromises.push(geocodeCepForCard(card));
      }
    });
    try {
      await Promise.race([Promise.all(geocodePromises), new Promise((r) => setTimeout(r, 8000))]);
    } catch (e) {
    }

    adCards.forEach(card => {
      const statusEl = card.querySelector('.ad-status');
      const adStatus = mapStatus(statusEl?.dataset?.status ?? statusEl?.textContent ?? '');

      const adName = normalize(card.querySelector('.ad-name')?.textContent ?? '');

      const speciesSexEl = card.querySelector('.ad-species');
      let adSpecies = '', adSex = '';
      if (speciesSexEl) {
        const raw = speciesSexEl.textContent || '';
        const parts = raw.includes('•') ? raw.split('•') : raw.split(/[-|]/);
        adSpecies = mapSpecies(parts[0] ?? '');
        adSex     = mapSex(parts[1] ?? '');
      }

      const adLat = parseCoordRaw(card.getAttribute('data-lat'));
      const adLon = parseCoordRaw(card.getAttribute('data-lon'));

      let showCard = true;
      if (filterStatuses.length > 0 && !filterStatuses.includes(adStatus)) showCard = false;
      if (showCard && filterName && !adName.includes(filterName)) showCard = false;
      if (showCard && filterTypes.length > 0 && !filterTypes.includes(adSpecies)) showCard = false;
      if (showCard && filterSexes.length > 0 && !filterSexes.includes(adSex)) showCard = false;

      if (showCard && canUseDistance(effectiveMax, userLat, userLon, adLat, adLon)) {
        const distancia = calcularDistancia(userLat, userLon, adLat, adLon);
        if (distancia > effectiveMax) showCard = false;
      }

      const container = card.closest('[data-ad-container]') || card.closest('.col-md-6') || card;
      container.style.display = showCard ? '' : 'none';
    });
  });
});