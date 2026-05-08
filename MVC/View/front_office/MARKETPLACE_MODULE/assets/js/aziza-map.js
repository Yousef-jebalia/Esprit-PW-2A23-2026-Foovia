document.addEventListener('DOMContentLoaded', () => {
  const mapElement = document.querySelector('[data-aziza-map]');
  const statusElement = document.querySelector('[data-aziza-map-status]');
  const citySelect = document.querySelector('[data-aziza-city-select]');
  const citySearch = document.querySelector('[data-aziza-city-search]');

  if (!mapElement || typeof L === 'undefined') return;

  const azizaLogo = mapElement.dataset.azizaLogo || '';
  const mgLogo = mapElement.dataset.mgLogo || '';
  const monoprixLogo = mapElement.dataset.monoprixLogo || '';
  const carrefourLogo = mapElement.dataset.carrefourLogo || '';
  const inventoryByBrand = (() => {
    try {
      const parsed = JSON.parse(mapElement.dataset.storeInventory || '{}');
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  })();
  const searchRadius = 25000;
  const liveApiTimeout = 10000;
  const defaultCity = 'tunis';
  const arabicAziza = '\\u0639\\u0632\\u064a\\u0632\\u0629';
  const cityCenters = {
    tunis: { lat: 36.8065, lng: 10.1815, label: 'Tunis' },
    ariana: { lat: 36.8625, lng: 10.1956, label: 'Ariana' },
    manouba: { lat: 36.8093, lng: 10.0863, label: 'Manouba' },
    ben_arous: { lat: 36.7531, lng: 10.2189, label: 'Ben Arous' },
    sousse: { lat: 35.8256, lng: 10.6084, label: 'Sousse' },
    sfax: { lat: 34.7406, lng: 10.7603, label: 'Sfax' }
  };

  const fallbackStores = {
    tunis: [
      { lat: 36.8061, lng: 10.1738, name: 'Aziza Tunis Centre', isAziza: true },
      { lat: 36.8127, lng: 10.1881, name: 'Aziza Bab El Khadra', isAziza: true },
      { lat: 36.8182, lng: 10.1702, name: 'Aziza Lafayette', isAziza: true },
      { lat: 36.7922, lng: 10.1748, name: 'Aziza Bab Jedid', isAziza: true },
      { lat: 36.8086, lng: 10.1354, name: 'Aziza Bardo', isAziza: true },
      { lat: 36.8519, lng: 10.2453, name: 'Aziza Les Berges du Lac', isAziza: true },
      { lat: 36.8189, lng: 10.1654, name: 'Monoprix Lafayette', isAziza: false },
      { lat: 36.7946, lng: 10.1847, name: 'Monoprix Avenue Habib Bourguiba', isAziza: false },
      { lat: 36.8361, lng: 10.1757, name: 'Monoprix Mutuelleville', isAziza: false },
      { lat: 36.8514, lng: 10.2071, name: 'Monoprix Berges du Lac', isAziza: false },
      { lat: 36.8345, lng: 10.1645, name: 'MG El Menzah', isAziza: false },
      { lat: 36.8245, lng: 10.1768, name: 'MG Mutuelleville', isAziza: false },
      { lat: 36.8024, lng: 10.1642, name: 'Carrefour Market Montfleury', isAziza: false },
      { lat: 36.8008, lng: 10.1859, name: 'Carrefour Market Tunis', isAziza: false },
      { lat: 36.8794, lng: 10.3238, name: 'Carrefour Market La Marsa', isAziza: false },
      { lat: 36.8181, lng: 10.3059, name: 'MG La Goulette', isAziza: false }
    ],
    ariana: [
      { lat: 36.8628, lng: 10.1958, name: 'Aziza Ariana', isAziza: true },
      { lat: 36.8854, lng: 10.1918, name: 'Aziza Ennasr 2', isAziza: true },
      { lat: 36.8742, lng: 10.1857, name: 'Aziza Ennasr 1', isAziza: true },
      { lat: 36.8579, lng: 10.1903, name: 'Aziza Ariana Ville', isAziza: true },
      { lat: 36.8438, lng: 10.1667, name: 'Aziza Mnihla', isAziza: true },
      { lat: 36.8759, lng: 10.1808, name: 'Monoprix Ennasr', isAziza: false },
      { lat: 36.8735, lng: 10.2062, name: 'Monoprix Riadh Andalous', isAziza: false },
      { lat: 36.8492, lng: 10.1925, name: 'MG Cite El Ghazala', isAziza: false },
      { lat: 36.8569, lng: 10.1805, name: 'MG Ariana Ville', isAziza: false },
      { lat: 36.8665, lng: 10.1648, name: 'Carrefour Market Ariana', isAziza: false },
      { lat: 36.8431, lng: 10.1707, name: 'Carrefour Market Mnihla', isAziza: false },
      { lat: 36.8924, lng: 10.1816, name: 'MG Borj Louzir', isAziza: false }
    ],
    manouba: [
      { lat: 36.8098, lng: 10.0874, name: 'Aziza Manouba', isAziza: true },
      { lat: 36.8186, lng: 10.0702, name: 'Aziza Manouba Centre', isAziza: true },
      { lat: 36.8331, lng: 10.0961, name: 'Aziza Douar Hicher', isAziza: true },
      { lat: 36.7908, lng: 10.0831, name: 'Aziza Oued Ellil', isAziza: true },
      { lat: 36.8057, lng: 10.0989, name: 'Monoprix Manouba', isAziza: false },
      { lat: 36.8342, lng: 10.0957, name: 'Monoprix Douar Hicher', isAziza: false },
      { lat: 36.8273, lng: 10.1026, name: 'MG Douar Hicher', isAziza: false },
      { lat: 36.7904, lng: 10.0835, name: 'MG Oued Ellil', isAziza: false },
      { lat: 36.7993, lng: 10.1112, name: 'Carrefour Market Manouba', isAziza: false },
      { lat: 36.8241, lng: 10.0632, name: 'MG Tebourba Road', isAziza: false }
    ],
    ben_arous: [
      { lat: 36.7527, lng: 10.2196, name: 'Aziza Ben Arous', isAziza: true },
      { lat: 36.7354, lng: 10.2043, name: 'Aziza Rades', isAziza: true },
      { lat: 36.7236, lng: 10.2175, name: 'Aziza Ezzahra', isAziza: true },
      { lat: 36.7117, lng: 10.1918, name: 'Aziza Megrine', isAziza: true },
      { lat: 36.7407, lng: 10.2382, name: 'Aziza Hammam Lif', isAziza: true },
      { lat: 36.7657, lng: 10.2297, name: 'Monoprix Ben Arous', isAziza: false },
      { lat: 36.7409, lng: 10.2332, name: 'MG Hammam Lif', isAziza: false },
      { lat: 36.7275, lng: 10.2148, name: 'MG Rades', isAziza: false },
      { lat: 36.7436, lng: 10.2359, name: 'Carrefour Market Hammam Lif', isAziza: false },
      { lat: 36.7112, lng: 10.1913, name: 'Carrefour Market Megrine', isAziza: false },
      { lat: 36.7435, lng: 10.2101, name: 'Magasin General Ben Arous', isAziza: false }
    ],
    sousse: [
      { lat: 35.8254, lng: 10.6088, name: 'Aziza Sousse', isAziza: true },
      { lat: 35.8598, lng: 10.5945, name: 'Aziza Hammam Sousse', isAziza: true },
      { lat: 35.8426, lng: 10.6034, name: 'Aziza Khezama', isAziza: true },
      { lat: 35.8263, lng: 10.6387, name: 'Aziza Sahloul', isAziza: true },
      { lat: 35.7658, lng: 10.8113, name: 'Aziza Monastir Road', isAziza: true },
      { lat: 35.8421, lng: 10.5989, name: 'Monoprix Khezama', isAziza: false },
      { lat: 35.8269, lng: 10.6397, name: 'Monoprix Sahloul', isAziza: false },
      { lat: 35.8352, lng: 10.6161, name: 'MG Sousse', isAziza: false },
      { lat: 35.8367, lng: 10.5938, name: 'MG Khezama', isAziza: false },
      { lat: 35.8137, lng: 10.6251, name: 'Carrefour Market Sousse', isAziza: false },
      { lat: 35.8582, lng: 10.5921, name: 'Carrefour Market Hammam Sousse', isAziza: false },
      { lat: 35.8456, lng: 10.6267, name: 'Magasin General Sousse Medina', isAziza: false }
    ],
    sfax: [
      { lat: 34.7399, lng: 10.7591, name: 'Aziza Sfax', isAziza: true },
      { lat: 34.7608, lng: 10.7694, name: 'Aziza Sakiet Ezzit', isAziza: true },
      { lat: 34.7471, lng: 10.7812, name: 'Aziza Route Gremda', isAziza: true },
      { lat: 34.7194, lng: 10.7417, name: 'Aziza Sfax Sud', isAziza: true },
      { lat: 34.7538, lng: 10.7465, name: 'Aziza Sfax Nord', isAziza: true },
      { lat: 34.7532, lng: 10.7476, name: 'Monoprix Sfax Nord', isAziza: false },
      { lat: 34.7189, lng: 10.7412, name: 'Monoprix Sfax Sud', isAziza: false },
      { lat: 34.7357, lng: 10.7605, name: 'MG Sfax', isAziza: false },
      { lat: 34.7478, lng: 10.7814, name: 'MG Route Gremda', isAziza: false },
      { lat: 34.7281, lng: 10.7489, name: 'Carrefour Market Sfax', isAziza: false },
      { lat: 34.7569, lng: 10.7681, name: 'Carrefour Market Sakiet Ezzit', isAziza: false },
      { lat: 34.7417, lng: 10.7532, name: 'Magasin General Sfax Centre', isAziza: false }
    ]
  };

  const apiEndpoints = [
    'https://overpass-api.de/api/interpreter',
    'https://overpass.kumi.systems/api/interpreter'
  ];

  const markerLayer = L.layerGroup();
  let userLocationMarker = null;
  const prettyTileConfig = {
    url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
  };

  const setStatus = (message) => {
    if (statusElement) statusElement.textContent = message;
  };

  const map = L.map(mapElement, {
    scrollWheelZoom: false
  }).setView([cityCenters[defaultCity].lat, cityCenters[defaultCity].lng], 13);

  markerLayer.addTo(map);

  L.tileLayer(prettyTileConfig.url, {
    attribution: prettyTileConfig.attribution,
    subdomains: 'abcd',
    maxZoom: 20
  }).addTo(map);

  const userIcon = L.divIcon({
    className: 'foovia-user-map-pin',
    html: '<span></span>',
    iconSize: [22, 22],
    iconAnchor: [11, 11]
  });

  const brandDefinitions = {
    aziza: { label: 'AZ', logo: azizaLogo, className: 'foovia-brand-aziza', tooltipClass: 'foovia-tooltip-aziza' },
    mg: { label: 'MG', logo: mgLogo, className: 'foovia-brand-mg', tooltipClass: 'foovia-tooltip-mg' },
    monoprix: { label: 'MP', logo: monoprixLogo, className: 'foovia-brand-monoprix', tooltipClass: 'foovia-tooltip-monoprix' },
    carrefour: { label: 'CR', logo: carrefourLogo, className: 'foovia-brand-carrefour', tooltipClass: 'foovia-tooltip-carrefour' },
    market: { label: 'MK', logo: '', className: 'foovia-brand-market', tooltipClass: 'foovia-tooltip-market' }
  };

  const isAzizaName = (name) => new RegExp(`aziza|${arabicAziza}`, 'i').test(String(name || ''));
  const isMgName = (name) => /(^|\s)mg(\s|$)|magasin general/i.test(String(name || ''));
  const isMonoprixName = (name) => /monoprix/i.test(String(name || ''));
  const isCarrefourName = (name) => /carrefour/i.test(String(name || ''));

  const detectBrand = (name) => {
    if (isAzizaName(name)) return 'aziza';
    if (isMgName(name)) return 'mg';
    if (isMonoprixName(name)) return 'monoprix';
    if (isCarrefourName(name)) return 'carrefour';
    return 'market';
  };

  const allSavedStores = Object.values(fallbackStores)
    .flat()
    .filter((store, index, stores) => stores.findIndex((candidate) =>
      candidate.name === store.name
      && candidate.lat === store.lat
      && candidate.lng === store.lng
    ) === index)
    .map((store) => ({
      ...store,
      brand: detectBrand(store.name)
    }));

  const createBrandIcon = (brandKey) => {
    const brand = brandDefinitions[brandKey] || brandDefinitions.market;
    return L.divIcon({
      className: 'foovia-market-map-pin',
      html: `<div class="foovia-market-pin-body ${brand.className}">${brand.logo ? `<img src="${brand.logo}" alt="${brandKey}">` : `<span>${brand.label}</span>`}</div>`,
      iconSize: [46, 54],
      iconAnchor: [23, 54],
      popupAnchor: [0, -54]
    });
  };

  const displayStoreName = (name) => {
    if (isAzizaName(name)) return 'Aziza';
    return String(name || 'Market');
  };

  const stockListHtml = (brandKey) => {
    const items = Array.isArray(inventoryByBrand[brandKey]) ? inventoryByBrand[brandKey] : [];
    if (!items.length) {
      return '<div class="foovia-store-stock-empty">No stock data linked yet.</div>';
    }

    return `
      <div class="foovia-store-stock-list">
        ${items.map((item) => `
          <div class="foovia-store-stock-row">
            <span>${item.name}</span>
            <b>${item.quantity}</b>
          </div>
        `).join('')}
      </div>
    `;
  };

  const tooltipHtml = (name, brandKey = 'market') => `
    <div class="foovia-aziza-tooltip">
      ${brandDefinitions[brandKey]?.logo ? `<img src="${brandDefinitions[brandKey].logo}" alt="${name}">` : `<span>${brandDefinitions[brandKey]?.label || 'MK'}</span>`}
      <div class="foovia-store-tooltip-copy">
        <strong>${displayStoreName(name)}</strong>
        <small>In stock now</small>
        ${stockListHtml(brandKey)}
      </div>
    </div>
  `;

  const normalizeStores = (elements) => {
    const seen = new Set();
    return elements
      .map((item) => {
        const lat = item.lat || item.center?.lat;
        const lng = item.lon || item.center?.lon;
        if (!lat || !lng) return null;

        const rawName = item.tags?.name || item.tags?.brand || item.tags?.operator || 'Market';
        const name = displayStoreName(rawName);
        const key = `${Number(lat).toFixed(5)}:${Number(lng).toFixed(5)}:${name.toLowerCase()}`;
        if (seen.has(key)) return null;
        seen.add(key);

        return {
          lat,
          lng,
          name,
          brand: detectBrand(rawName)
        };
      })
      .filter(Boolean);
  };

  const closestCityKey = (center) => Object.entries(cityCenters)
    .map(([key, city]) => ({
      key,
      distance: Math.hypot(center.lat - city.lat, center.lng - city.lng)
    }))
    .sort((a, b) => a.distance - b.distance)[0]?.key || defaultCity;

  const addMarketMarkers = (stores, center, statusPrefix) => {
    markerLayer.clearLayers();
    userLocationMarker = L.marker([center.lat, center.lng], { icon: userIcon }).addTo(markerLayer).bindTooltip('Your location');

    const bounds = L.latLngBounds([[center.lat, center.lng]]);
    stores.forEach((store) => {
      const brand = store.brand || detectBrand(store.name);
      bounds.extend([store.lat, store.lng]);
      L.marker([store.lat, store.lng], { icon: createBrandIcon(brand) })
        .addTo(markerLayer)
        .bindPopup(tooltipHtml(store.name, brand), {
          autoPan: true,
          closeButton: false,
          className: `foovia-aziza-tooltip-wrap ${brandDefinitions[brand]?.tooltipClass || 'foovia-tooltip-market'}`,
          offset: [0, -44],
          maxWidth: 260,
          minWidth: 220
        });
    });

    map.fitBounds(bounds, { padding: [36, 36], maxZoom: 15 });
    setStatus('Markets loaded.');
  };

  const addNationwideMarkers = (stores, statusPrefix, userCenter = null, focusOnUser = false) => {
    markerLayer.clearLayers();

    const bounds = L.latLngBounds([]);
    if (userCenter) {
      userLocationMarker = L.marker([userCenter.lat, userCenter.lng], { icon: userIcon }).addTo(markerLayer).bindTooltip('Your location');
      bounds.extend([userCenter.lat, userCenter.lng]);
    }
    stores.forEach((store) => {
      const brand = store.brand || detectBrand(store.name);
      bounds.extend([store.lat, store.lng]);
      L.marker([store.lat, store.lng], { icon: createBrandIcon(brand) })
        .addTo(markerLayer)
        .bindPopup(tooltipHtml(store.name, brand), {
          autoPan: true,
          closeButton: false,
          className: `foovia-aziza-tooltip-wrap ${brandDefinitions[brand]?.tooltipClass || 'foovia-tooltip-market'}`,
          offset: [0, -44],
          maxWidth: 260,
          minWidth: 220
        });
    });

    if (userCenter && focusOnUser) {
      map.setView([userCenter.lat, userCenter.lng], 11);
    } else if (bounds.isValid()) {
      map.fitBounds(bounds, { padding: [42, 42], maxZoom: 8 });
    }

    setStatus('Markets loaded.');
  };

  const buildQuery = (center) => `
    [out:json][timeout:25];
    (
      node(around:${searchRadius},${center.lat},${center.lng})["shop"~"supermarket|convenience|grocery|greengrocer|department_store",i];
      way(around:${searchRadius},${center.lat},${center.lng})["shop"~"supermarket|convenience|grocery|greengrocer|department_store",i];
      relation(around:${searchRadius},${center.lat},${center.lng})["shop"~"supermarket|convenience|grocery|greengrocer|department_store",i];
      node(around:${searchRadius},${center.lat},${center.lng})["name"~"aziza|aziza market|${arabicAziza}",i];
      way(around:${searchRadius},${center.lat},${center.lng})["name"~"aziza|aziza market|${arabicAziza}",i];
      relation(around:${searchRadius},${center.lat},${center.lng})["name"~"aziza|aziza market|${arabicAziza}",i];
      node(around:${searchRadius},${center.lat},${center.lng})["brand"~"aziza|${arabicAziza}",i];
      way(around:${searchRadius},${center.lat},${center.lng})["brand"~"aziza|${arabicAziza}",i];
      node(around:${searchRadius},${center.lat},${center.lng})["operator"~"aziza|${arabicAziza}",i];
    );
    out center tags;
  `;

  const fetchMarketStores = async (center) => {
    const query = buildQuery(center);
    let lastError = null;

    for (const endpoint of apiEndpoints) {
      try {
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), liveApiTimeout);
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'text/plain;charset=UTF-8' },
          body: query,
          signal: controller.signal
        });
        window.clearTimeout(timeout);
        if (!response.ok) throw new Error(`Map API failed: ${response.status}`);
        const data = await response.json();
        return normalizeStores(data.elements || []);
      } catch (error) {
        lastError = error;
      }
    }

    throw lastError || new Error('Map API failed');
  };

  const loadStores = async (center, cityKey = closestCityKey(center), label = 'near this area') => {
    map.setView([center.lat, center.lng], 13);
    const savedStores = fallbackStores[cityKey] || [];
    addMarketMarkers(savedStores, center, `Saved markets ${label}`);
    setStatus('Markets loaded.');

    try {
      const stores = await fetchMarketStores(center);
      if (stores.length > 0) {
        addMarketMarkers(stores, center, 'Live API result');
        return;
      }

      setStatus('Markets loaded.');
    } catch (error) {
      setStatus('Markets loaded.');
    }
  };

  const loadSelectedCity = () => {
    const selected = citySelect?.value || 'auto';
    if (selected === 'all') {
      addNationwideMarkers(allSavedStores, 'All saved markets');
      return;
    }
    if (selected !== 'auto') {
      const city = cityCenters[selected] || cityCenters[defaultCity];
      loadStores(city, selected, `around ${city.label}`);
      return;
    }

    loadBrowserLocation();
  };

  const loadBrowserLocation = () => {
    if (!('geolocation' in navigator)) {
      const city = cityCenters[defaultCity];
      setStatus('Markets loaded.');
      loadStores(city, 'tunis', 'around Tunis');
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const center = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        if ((citySelect?.value || 'auto') === 'all') {
          addNationwideMarkers(allSavedStores, 'All saved markets', center, false);
          setStatus('Markets loaded.');
          return;
        }
        loadStores(center, closestCityKey(center), 'near your browser location');
      },
      () => {
        const city = cityCenters.tunis;
        setStatus('Markets loaded.');
        loadStores(city, 'tunis', 'around Tunis');
      },
      { enableHighAccuracy: true, timeout: 9000 }
    );
  };

  if (citySearch) citySearch.addEventListener('click', loadSelectedCity);
  if (citySelect) citySelect.addEventListener('change', loadSelectedCity);

  if (citySelect) {
    citySelect.value = 'all';
  }
  loadBrowserLocation();
});
