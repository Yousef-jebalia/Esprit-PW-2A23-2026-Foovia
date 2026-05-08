document.addEventListener('DOMContentLoaded', () => {
  const cartStorageKey = 'fooviaCartItems';
  const deliveryPlanKey = 'fooviaDeliveryPlan';
  const loadCart = () => {
    try {
      const savedCart = JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
      return Array.isArray(savedCart) ? savedCart : [];
    } catch (error) {
      return [];
    }
  };
  const cart = loadCart();
  const cartCount = document.querySelector('[data-cart-count]');
  const cartModal = document.querySelector('[data-cart-modal]');
  const cartItems = document.querySelector('[data-cart-items]');
  const cartTotal = document.querySelector('[data-cart-total]');
  const picker = document.querySelector('[data-cart-picker]');
  const pickerStore = document.querySelector('[data-picker-store]');
  const pickerQuantity = document.querySelector('[data-picker-quantity]');
  const pickerName = document.querySelector('[data-picker-product-name]');
  const pickerPrice = document.querySelector('[data-picker-price]');
  const pickerReservationTotal = document.querySelector('[data-picker-reservation-total]');
  const floatingCartButtons = Array.from(document.querySelectorAll('[data-cart-toggle]'));
  const deliveryPlanner = document.querySelector('[data-delivery-planner]');
  const deliveryMapElement = document.querySelector('[data-delivery-map]');
  const deliveryPointNode = document.querySelector('[data-delivery-point]');
  const deliveryDestinationNode = document.querySelector('[data-delivery-destination]');
  const deliveryEstimateNode = document.querySelector('[data-delivery-estimate]');
  const deliveryFeedbackNode = document.querySelector('[data-delivery-feedback]');
  const deliveryWeatherBadgeNode = document.querySelector('[data-delivery-weather-badge]');
  const deliveryWeatherNoteNode = document.querySelector('[data-delivery-weather-note]');
  const deliveryCashButton = document.querySelector('[data-delivery-cash]');
  const deliveryCardButton = document.querySelector('[data-delivery-card]');
  const deliveryMethodButtons = Array.from(document.querySelectorAll('[data-delivery-method]'));
  const rawSubscription = String(window.FOOVIA_USER_SUBSCRIPTION || 'normal').trim().toLowerCase();
  const canUseDelivery = window.FOOVIA_CAN_DELIVER === true || rawSubscription.includes('premium') || rawSubscription.includes('premuim');
  const premiumOnlyMessage = 'Premium plan required for cart and delivery. You can still reserve this item.';
  let dragPreview = null;
  let pendingProduct = null;
  let plannerMap = null;
  let plannerLayer = null;
  let destinationMarker = null;
  let routeLine = null;
  let selectedHub = null;
  let selectedPaymentMethod = '';
  let deliveryDestination = { lat: 36.8065, lng: 10.1815, label: 'Your current location' };
  let currentEtaMinutes = null;
  let currentDistanceKm = null;
  let plannerRequestToken = 0;
  const maxDeliveryDistanceKm = 100;
  let activeDeliveryHubs = [];
  const deliveryTrackerKey = 'fooviaDeliveryTracker';
  const baseDeliveryFee = 7.5;
  let currentDeliveryFee = baseDeliveryFee;
  let currentWeatherAdjustment = {
    label: 'Standard delivery conditions',
    surcharge: 0,
    summary: 'No weather surcharge applied.'
  };

  const deliveryHubs = [
    { id: 'aziza_tunis_centre', name: 'Aziza Tunis Centre', brand: 'Aziza', lat: 36.8061, lng: 10.1738 },
    { id: 'aziza_bab_khadra', name: 'Aziza Bab El Khadra', brand: 'Aziza', lat: 36.8127, lng: 10.1881 },
    { id: 'aziza_lafayette', name: 'Aziza Lafayette', brand: 'Aziza', lat: 36.8182, lng: 10.1702 },
    { id: 'aziza_bab_jedid', name: 'Aziza Bab Jedid', brand: 'Aziza', lat: 36.7922, lng: 10.1748 },
    { id: 'aziza_bardo', name: 'Aziza Bardo', brand: 'Aziza', lat: 36.8086, lng: 10.1354 },
    { id: 'aziza_lac', name: 'Aziza Les Berges du Lac', brand: 'Aziza', lat: 36.8519, lng: 10.2453 },
    { id: 'monoprix_lafayette', name: 'Monoprix Lafayette', brand: 'Monoprix', lat: 36.8189, lng: 10.1654 },
    { id: 'monoprix_bourguiba', name: 'Monoprix Avenue Habib Bourguiba', brand: 'Monoprix', lat: 36.7946, lng: 10.1847 },
    { id: 'monoprix_mutuelleville', name: 'Monoprix Mutuelleville', brand: 'Monoprix', lat: 36.8361, lng: 10.1757 },
    { id: 'monoprix_lac', name: 'Monoprix Berges du Lac', brand: 'Monoprix', lat: 36.8514, lng: 10.2071 },
    { id: 'mg_menzah', name: 'MG El Menzah', brand: 'MG', lat: 36.8345, lng: 10.1645 },
    { id: 'mg_mutuelleville', name: 'MG Mutuelleville', brand: 'MG', lat: 36.8245, lng: 10.1768 },
    { id: 'carrefour_montfleury', name: 'Carrefour Market Montfleury', brand: 'Carrefour', lat: 36.8024, lng: 10.1642 },
    { id: 'carrefour_tunis', name: 'Carrefour Market Tunis', brand: 'Carrefour', lat: 36.8008, lng: 10.1859 },
    { id: 'carrefour_marsa', name: 'Carrefour Market La Marsa', brand: 'Carrefour', lat: 36.8794, lng: 10.3238 },
    { id: 'mg_goulette', name: 'MG La Goulette', brand: 'MG', lat: 36.8181, lng: 10.3059 },
    { id: 'citymarket_centre', name: 'City Market Centre Urbain', brand: 'City Market', lat: 36.8427, lng: 10.1906 },

    { id: 'aziza_ariana', name: 'Aziza Ariana', brand: 'Aziza', lat: 36.8628, lng: 10.1958 },
    { id: 'aziza_ennasr2', name: 'Aziza Ennasr 2', brand: 'Aziza', lat: 36.8854, lng: 10.1918 },
    { id: 'aziza_ennasr1', name: 'Aziza Ennasr 1', brand: 'Aziza', lat: 36.8742, lng: 10.1857 },
    { id: 'aziza_ariana_ville', name: 'Aziza Ariana Ville', brand: 'Aziza', lat: 36.8579, lng: 10.1903 },
    { id: 'aziza_mnihla', name: 'Aziza Mnihla', brand: 'Aziza', lat: 36.8438, lng: 10.1667 },
    { id: 'monoprix_ennasr', name: 'Monoprix Ennasr', brand: 'Monoprix', lat: 36.8759, lng: 10.1808 },
    { id: 'monoprix_riadh_andalous', name: 'Monoprix Riadh Andalous', brand: 'Monoprix', lat: 36.8735, lng: 10.2062 },
    { id: 'mg_ghazala', name: 'MG Cite El Ghazala', brand: 'MG', lat: 36.8492, lng: 10.1925 },
    { id: 'mg_ariana_ville', name: 'MG Ariana Ville', brand: 'MG', lat: 36.8569, lng: 10.1805 },
    { id: 'mg_borj_louzir', name: 'MG Borj Louzir', brand: 'MG', lat: 36.8924, lng: 10.1816 },
    { id: 'carrefour_ariana', name: 'Carrefour Market Ariana', brand: 'Carrefour', lat: 36.8665, lng: 10.1648 },
    { id: 'carrefour_mnihla', name: 'Carrefour Market Mnihla', brand: 'Carrefour', lat: 36.8431, lng: 10.1707 },

    { id: 'aziza_manouba', name: 'Aziza Manouba', brand: 'Aziza', lat: 36.8098, lng: 10.0874 },
    { id: 'aziza_manouba_centre', name: 'Aziza Manouba Centre', brand: 'Aziza', lat: 36.8186, lng: 10.0702 },
    { id: 'aziza_douar_hicher', name: 'Aziza Douar Hicher', brand: 'Aziza', lat: 36.8331, lng: 10.0961 },
    { id: 'aziza_oued_ellil', name: 'Aziza Oued Ellil', brand: 'Aziza', lat: 36.7908, lng: 10.0831 },
    { id: 'monoprix_manouba', name: 'Monoprix Manouba', brand: 'Monoprix', lat: 36.8057, lng: 10.0989 },
    { id: 'monoprix_douar_hicher', name: 'Monoprix Douar Hicher', brand: 'Monoprix', lat: 36.8342, lng: 10.0957 },
    { id: 'mg_douar_hicher', name: 'MG Douar Hicher', brand: 'MG', lat: 36.8273, lng: 10.1026 },
    { id: 'mg_oued_ellil', name: 'MG Oued Ellil', brand: 'MG', lat: 36.7904, lng: 10.0835 },
    { id: 'carrefour_manouba', name: 'Carrefour Market Manouba', brand: 'Carrefour', lat: 36.7993, lng: 10.1112 },
    { id: 'mg_tebourba_road', name: 'MG Tebourba Road', brand: 'MG', lat: 36.8241, lng: 10.0632 },

    { id: 'aziza_ben_arous', name: 'Aziza Ben Arous', brand: 'Aziza', lat: 36.7527, lng: 10.2196 },
    { id: 'aziza_rades', name: 'Aziza Rades', brand: 'Aziza', lat: 36.7354, lng: 10.2043 },
    { id: 'aziza_ezzahra', name: 'Aziza Ezzahra', brand: 'Aziza', lat: 36.7236, lng: 10.2175 },
    { id: 'aziza_megrine', name: 'Aziza Megrine', brand: 'Aziza', lat: 36.7117, lng: 10.1918 },
    { id: 'aziza_hammam_lif', name: 'Aziza Hammam Lif', brand: 'Aziza', lat: 36.7407, lng: 10.2382 },
    { id: 'monoprix_ben_arous', name: 'Monoprix Ben Arous', brand: 'Monoprix', lat: 36.7657, lng: 10.2297 },
    { id: 'mg_hammam_lif', name: 'MG Hammam Lif', brand: 'MG', lat: 36.7409, lng: 10.2332 },
    { id: 'mg_rades', name: 'MG Rades', brand: 'MG', lat: 36.7275, lng: 10.2148 },
    { id: 'carrefour_hammam_lif', name: 'Carrefour Market Hammam Lif', brand: 'Carrefour', lat: 36.7436, lng: 10.2359 },
    { id: 'carrefour_megrine', name: 'Carrefour Market Megrine', brand: 'Carrefour', lat: 36.7112, lng: 10.1913 },
    { id: 'mg_ben_arous_general', name: 'Magasin General Ben Arous', brand: 'MG', lat: 36.7435, lng: 10.2101 },

    { id: 'aziza_sousse', name: 'Aziza Sousse', brand: 'Aziza', lat: 35.8254, lng: 10.6088 },
    { id: 'aziza_hammam_sousse', name: 'Aziza Hammam Sousse', brand: 'Aziza', lat: 35.8598, lng: 10.5945 },
    { id: 'aziza_khezama', name: 'Aziza Khezama', brand: 'Aziza', lat: 35.8426, lng: 10.6034 },
    { id: 'aziza_sahloul', name: 'Aziza Sahloul', brand: 'Aziza', lat: 35.8263, lng: 10.6387 },
    { id: 'aziza_monastir_road', name: 'Aziza Monastir Road', brand: 'Aziza', lat: 35.7658, lng: 10.8113 },
    { id: 'monoprix_khezama', name: 'Monoprix Khezama', brand: 'Monoprix', lat: 35.8421, lng: 10.5989 },
    { id: 'monoprix_sahloul', name: 'Monoprix Sahloul', brand: 'Monoprix', lat: 35.8269, lng: 10.6397 },
    { id: 'mg_sousse', name: 'MG Sousse', brand: 'MG', lat: 35.8352, lng: 10.6161 },
    { id: 'mg_khezama', name: 'MG Khezama', brand: 'MG', lat: 35.8367, lng: 10.5938 },
    { id: 'carrefour_sousse', name: 'Carrefour Market Sousse', brand: 'Carrefour', lat: 35.8137, lng: 10.6251 },
    { id: 'carrefour_hammam_sousse', name: 'Carrefour Market Hammam Sousse', brand: 'Carrefour', lat: 35.8582, lng: 10.5921 },
    { id: 'mg_sousse_medina', name: 'Magasin General Sousse Medina', brand: 'MG', lat: 35.8456, lng: 10.6267 },

    { id: 'aziza_sfax', name: 'Aziza Sfax', brand: 'Aziza', lat: 34.7399, lng: 10.7591 },
    { id: 'aziza_sakiet_ezzit', name: 'Aziza Sakiet Ezzit', brand: 'Aziza', lat: 34.7608, lng: 10.7694 },
    { id: 'aziza_gremda', name: 'Aziza Route Gremda', brand: 'Aziza', lat: 34.7471, lng: 10.7812 },
    { id: 'aziza_sfax_sud', name: 'Aziza Sfax Sud', brand: 'Aziza', lat: 34.7194, lng: 10.7417 },
    { id: 'aziza_sfax_nord', name: 'Aziza Sfax Nord', brand: 'Aziza', lat: 34.7538, lng: 10.7465 },
    { id: 'monoprix_sfax_nord', name: 'Monoprix Sfax Nord', brand: 'Monoprix', lat: 34.7532, lng: 10.7476 },
    { id: 'monoprix_sfax_sud', name: 'Monoprix Sfax Sud', brand: 'Monoprix', lat: 34.7189, lng: 10.7412 },
    { id: 'mg_sfax', name: 'MG Sfax', brand: 'MG', lat: 34.7357, lng: 10.7605 },
    { id: 'mg_route_gremda', name: 'MG Route Gremda', brand: 'MG', lat: 34.7478, lng: 10.7814 },
    { id: 'carrefour_sfax', name: 'Carrefour Market Sfax', brand: 'Carrefour', lat: 34.7281, lng: 10.7489 },
    { id: 'carrefour_sakiet_ezzit', name: 'Carrefour Market Sakiet Ezzit', brand: 'Carrefour', lat: 34.7569, lng: 10.7681 },
    { id: 'mg_sfax_centre', name: 'Magasin General Sfax Centre', brand: 'MG', lat: 34.7417, lng: 10.7532 }
  ];

  const parseQuantity = (value) => {
    const quantity = Number.parseFloat(String(value || '1').replace(',', '.'));
    return Number.isFinite(quantity) && quantity > 0 ? quantity : 1;
  };

  const formatPrice = (value) => {
    const price = Number(value || 0);
    return price.toFixed(3).replace(/\.?0+$/, '');
  };

  const productUnit = (product) => product?.unit || 'kg';
  const formatUnitPrice = (price, unit = 'kg') => `${formatPrice(price)} TND / ${unit}`;
  const formatQuantity = (quantity, unit = 'kg') => {
    const cleanQuantity = parseQuantity(quantity);
    if (unit === 'piece') {
      return `${cleanQuantity} ${cleanQuantity === 1 ? 'piece' : 'pieces'}`;
    }

    return `${cleanQuantity} ${unit}`;
  };
  const formatQuantityTotal = (price, quantity, unit = 'kg') => {
    const cleanQuantity = parseQuantity(quantity);
    return `Total ${formatPrice(Number(price || 0) * cleanQuantity)} TND for ${formatQuantity(cleanQuantity, unit)}`;
  };

  const formatWeatherLabel = (payload) => {
    const code = Number(payload?.weather_code ?? 0);
    const rain = Number(payload?.rain ?? 0) + Number(payload?.showers ?? 0);
    const snow = Number(payload?.snowfall ?? 0);
    const wind = Math.max(Number(payload?.wind_speed_10m ?? 0), Number(payload?.wind_gusts_10m ?? 0));

    if ([95, 96, 99].includes(code)) return 'Storm alert';
    if (snow > 0 || [71, 73, 75, 77, 85, 86].includes(code)) return 'Snowy';
    if (rain > 4 || [65, 67, 82].includes(code)) return 'Heavy rain';
    if (rain > 0 || [51, 53, 55, 56, 57, 61, 63, 66, 80, 81].includes(code)) return 'Rainy';
    if (wind >= 45) return 'Very windy';
    if (wind >= 30) return 'Windy';
    if ([45, 48].includes(code)) return 'Foggy';
    if ([1, 2, 3].includes(code)) return 'Cloudy';
    return 'Clear';
  };

  const buildWeatherAdjustment = (payload) => {
    const code = Number(payload?.weather_code ?? 0);
    const precipitation = Number(payload?.precipitation ?? 0);
    const rain = Number(payload?.rain ?? 0) + Number(payload?.showers ?? 0);
    const snow = Number(payload?.snowfall ?? 0);
    const windSpeed = Number(payload?.wind_speed_10m ?? 0);
    const windGust = Number(payload?.wind_gusts_10m ?? 0);
    const wind = Math.max(windSpeed, windGust);
    const label = formatWeatherLabel(payload);

    let surcharge = 0;
    let summary = 'No weather surcharge applied.';

    if ([95, 96, 99].includes(code) || rain >= 6 || snow >= 2 || wind >= 60) {
      surcharge = 1.8;
      summary = 'Severe conditions detected. Delivery gets a higher-risk surcharge.';
    } else if (rain >= 2 || snow > 0 || [61, 63, 65, 66, 67, 71, 73, 75, 80, 81, 82, 85, 86].includes(code) || wind >= 40) {
      surcharge = 1.1;
      summary = 'Rain or strong wind is slowing delivery slightly.';
    } else if (precipitation > 0 || [45, 48, 51, 53, 55, 56, 57].includes(code) || wind >= 28) {
      surcharge = 0.45;
      summary = 'Light weather friction adds a small delivery surcharge.';
    }

    return {
      label,
      surcharge,
      summary,
      weatherCode: code,
      windSpeed,
      windGust,
      precipitation,
      fee: baseDeliveryFee + surcharge
    };
  };

  const fetchWeatherAdjustment = async (destination) => {
    const query = new URLSearchParams({
      latitude: String(destination.lat),
      longitude: String(destination.lng),
      current: 'weather_code,precipitation,rain,showers,snowfall,wind_speed_10m,wind_gusts_10m',
      timezone: 'auto',
      wind_speed_unit: 'kmh'
    });

    const response = await fetch(`https://api.open-meteo.com/v1/forecast?${query.toString()}`, { method: 'GET' });
    if (!response.ok) {
      throw new Error(`Weather request failed: ${response.status}`);
    }

    const payload = await response.json();
    if (!payload?.current) {
      throw new Error('Weather data missing current conditions.');
    }

    return buildWeatherAdjustment(payload.current);
  };

  const saveCart = () => {
    localStorage.setItem(cartStorageKey, JSON.stringify(cart));
  };

  const saveDeliveryPlan = (plan) => {
    localStorage.setItem(deliveryPlanKey, JSON.stringify(plan));
  };

  const ensureNotificationPermission = async () => {
    if (!('Notification' in window)) {
      return false;
    }

    if (Notification.permission === 'granted') {
      return true;
    }

    if (Notification.permission === 'denied') {
      return false;
    }

    try {
      return (await Notification.requestPermission()) === 'granted';
    } catch (error) {
      return false;
    }
  };

  const plannerTileConfig = {
    url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
  };

  const formatMinutes = (minutes) => {
    if (minutes < 1) {
      return `${Math.max(1, Math.round(minutes * 60))} sec`;
    }
    if (minutes < 60) {
      return `${minutes} min`;
    }
    const hours = Math.floor(minutes / 60);
    const remaining = minutes % 60;
    return remaining === 0 ? `${hours} h` : `${hours} h ${remaining} min`;
  };

  const haversineKm = (from, to) => {
    const toRad = (value) => (value * Math.PI) / 180;
    const earthRadius = 6371;
    const dLat = toRad(to.lat - from.lat);
    const dLng = toRad(to.lng - from.lng);
    const a = Math.sin(dLat / 2) ** 2
      + Math.cos(toRad(from.lat)) * Math.cos(toRad(to.lat)) * Math.sin(dLng / 2) ** 2;
    return 2 * earthRadius * Math.asin(Math.sqrt(a));
  };

  const estimateDeliveryMinutes = (hub, destination) => {
    if (hub.id === 'aziza_test_nearby') {
      return 20 / 60;
    }
    const km = haversineKm(hub, destination);

    if (km <= 1) {
      return 6;
    }
    if (km <= 3) {
      return Math.round(7 + (km * 2.2));
    }
    if (km <= 8) {
      return Math.round(10 + (km * 1.8));
    }
    if (km <= 20) {
      return Math.round(16 + (km * 1.2));
    }

    return Math.round(24 + (km * 0.95));
  };

  const isSelectionDeliverable = () => Number.isFinite(currentDistanceKm) && currentDistanceKm <= maxDeliveryDistanceKm;

  const detectStoreBrand = (name) => {
    const normalized = String(name || '').toLowerCase();
    if (normalized.includes('aziza')) return 'aziza';
    if (normalized.includes('monoprix')) return 'monoprix';
    if (normalized.includes('carrefour')) return 'carrefour';
    if (normalized.includes('city market')) return 'city market';
    if (normalized.includes('mg') || normalized.includes('magasin general')) return 'mg';
    if (normalized.includes('foovia')) return 'foovia';
    return normalized.trim();
  };

  const buildActiveDeliveryHubs = () => {
    const selectedBrands = new Set(
      cart
        .map((item) => detectStoreBrand(item.storeName))
        .filter((brand) => brand !== '')
    );

    if (selectedBrands.size === 0) {
      activeDeliveryHubs = deliveryHubs.slice();
      return;
    }

    activeDeliveryHubs = deliveryHubs.filter((hub) => selectedBrands.has(detectStoreBrand(hub.brand || hub.name)));
    if (activeDeliveryHubs.length === 0) {
      activeDeliveryHubs = deliveryHubs.slice();
    }

    if (Number.isFinite(deliveryDestination.lat) && Number.isFinite(deliveryDestination.lng)) {
      const testHub = {
        id: 'aziza_test_nearby',
        name: 'Aziza Test Nearby',
        brand: 'Aziza',
        lat: 36.901755,
        lng: 10.185466
      };

      activeDeliveryHubs = [testHub, ...activeDeliveryHubs.filter((hub) => hub.id !== testHub.id)];
    }
  };

  const drawRouteLine = (coordinates) => {
    if (!plannerMap || !plannerLayer) {
      return;
    }

    if (routeLine) {
      plannerLayer.removeLayer(routeLine);
      routeLine = null;
    }

    if (!Array.isArray(coordinates) || coordinates.length < 2) {
      return;
    }

    routeLine = L.polyline(
      coordinates.map(([lng, lat]) => [lat, lng]),
      {
        color: '#63c25b',
        weight: 5,
        opacity: 0.9,
        lineCap: 'round',
        lineJoin: 'round'
      }
    ).addTo(plannerLayer);
  };

  const fitPlannerToSelection = (hub, destination) => {
    if (!plannerMap || !hub) {
      return;
    }

    const bounds = L.latLngBounds([
      [hub.lat, hub.lng],
      [destination.lat, destination.lng]
    ]);
    plannerMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 13 });
  };

  const fitPlannerOverview = () => {
    if (!plannerMap || activeDeliveryHubs.length === 0) {
      return;
    }

    const points = activeDeliveryHubs.map((hub) => [hub.lat, hub.lng]);
    points.push([deliveryDestination.lat, deliveryDestination.lng]);
    const bounds = L.latLngBounds(points);
    plannerMap.fitBounds(bounds, { padding: [32, 32], maxZoom: 8 });
  };

  const fetchRouteEstimate = async (hub, destination) => {
    const response = await fetch(
      `https://router.project-osrm.org/route/v1/driving/${hub.lng},${hub.lat};${destination.lng},${destination.lat}?overview=full&geometries=geojson`,
      { method: 'GET' }
    );

    if (!response.ok) {
      throw new Error(`Routing failed: ${response.status}`);
    }

    const payload = await response.json();
    const route = payload?.routes?.[0];
    if (!route) {
      throw new Error('No route returned');
    }

    const distanceKm = Number(route.distance || 0) / 1000;
    const driveMinutes = Number(route.duration || 0) / 60;
    const preparationBuffer = distanceKm <= 1 ? 2 : distanceKm <= 3 ? 4 : distanceKm <= 8 ? 6 : distanceKm <= 20 ? 9 : 12;
    const cityHandlingBuffer = distanceKm <= 4 ? 2 : 3;
    const etaMinutes = Math.max(1, Math.round(driveMinutes + preparationBuffer + cityHandlingBuffer));

    return {
      etaMinutes,
      distanceKm,
      coordinates: route.geometry?.coordinates || []
    };
  };

  const updateDeliverySummary = () => {
    if (deliveryDestinationNode) {
      deliveryDestinationNode.textContent = deliveryDestination.label;
    }
    if (deliveryPointNode) {
      deliveryPointNode.textContent = selectedHub ? selectedHub.name : 'Choose a point on the map';
    }
    if (deliveryEstimateNode) {
      deliveryEstimateNode.textContent = selectedHub && Number.isFinite(currentEtaMinutes)
        ? formatMinutes(currentEtaMinutes)
        : 'Waiting for selection';
    }
    if (deliveryWeatherBadgeNode) {
      deliveryWeatherBadgeNode.textContent = `${currentWeatherAdjustment.label} · ${formatPrice(currentDeliveryFee)} TND`;
    }
    if (deliveryWeatherNoteNode) {
      deliveryWeatherNoteNode.textContent = currentWeatherAdjustment.summary;
    }
    const feeSummary = `Delivery fee: ${formatPrice(currentDeliveryFee)} TND. ${currentWeatherAdjustment.label}.`;
    if (deliveryCashButton) {
      deliveryCashButton.disabled = Boolean(selectedHub) && !isSelectionDeliverable();
      deliveryCashButton.dataset.deliveryFee = String(currentDeliveryFee);
    }
    if (deliveryCardButton) {
      deliveryCardButton.disabled = Boolean(selectedHub) && !isSelectionDeliverable();
      deliveryCardButton.dataset.deliveryFee = String(currentDeliveryFee);
    }
    if (deliveryFeedbackNode && selectedHub && Number.isFinite(currentEtaMinutes) && deliveryFeedbackNode.textContent === '') {
      deliveryFeedbackNode.textContent = feeSummary;
    }
  };

  const refreshDeliveryEstimate = async () => {
    if (!selectedHub) {
      currentEtaMinutes = null;
      updateDeliverySummary();
      return;
    }

    const requestToken = ++plannerRequestToken;
    currentDistanceKm = haversineKm(selectedHub, deliveryDestination);
    currentEtaMinutes = estimateDeliveryMinutes(selectedHub, deliveryDestination);
    currentDeliveryFee = baseDeliveryFee;
    currentWeatherAdjustment = {
      label: 'Checking live weather',
      surcharge: 0,
      summary: 'Foovia is checking live weather before finalizing the fee.'
    };
    updateDeliverySummary();

    if (selectedHub.id === 'aziza_test_nearby') {
      drawRouteLine(null);
      fitPlannerToSelection(selectedHub, deliveryDestination);
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `Aziza Test Nearby selected. Foovia is using a 20-second delivery test timer. Delivery fee: ${formatPrice(currentDeliveryFee)} TND.`;
      }
    } else if (!isSelectionDeliverable()) {
      drawRouteLine(null);
      fitPlannerToSelection(selectedHub, deliveryDestination);
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `${selectedHub.name} is about ${Math.round(currentDistanceKm)} km away. Please choose a market within ${maxDeliveryDistanceKm} km for delivery.`;
      }
      return;
    } else {
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `Checking route from ${selectedHub.name}...`;
      }

      try {
        const route = await fetchRouteEstimate(selectedHub, deliveryDestination);
        if (requestToken !== plannerRequestToken) {
          return;
        }

        currentDistanceKm = route.distanceKm;
        currentEtaMinutes = route.etaMinutes;
        drawRouteLine(route.coordinates);
        fitPlannerToSelection(selectedHub, deliveryDestination);
        updateDeliverySummary();
      } catch (error) {
        if (requestToken !== plannerRequestToken) {
          return;
        }

        drawRouteLine(null);
        fitPlannerToSelection(selectedHub, deliveryDestination);
        currentEtaMinutes = estimateDeliveryMinutes(selectedHub, deliveryDestination);
        updateDeliverySummary();
      }
    }

    try {
      const adjustment = await fetchWeatherAdjustment(deliveryDestination);
      if (requestToken !== plannerRequestToken) {
        return;
      }

      currentWeatherAdjustment = adjustment;
      currentDeliveryFee = adjustment.fee;
      updateDeliverySummary();
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `${selectedHub.name} selected. Weather: ${adjustment.label}. ${adjustment.summary} Delivery fee: ${formatPrice(currentDeliveryFee)} TND. ETA: ${formatMinutes(currentEtaMinutes)}.`;
      }
    } catch (error) {
      if (requestToken !== plannerRequestToken) {
        return;
      }

      currentWeatherAdjustment = {
        label: 'Weather unavailable',
        surcharge: 0,
        summary: 'Live weather could not be loaded, so Foovia kept the normal fee.'
      };
      currentDeliveryFee = baseDeliveryFee;
      updateDeliverySummary();
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `${selectedHub.name} selected. Live weather was unavailable, so Foovia kept the base delivery fee at ${formatPrice(currentDeliveryFee)} TND. ETA: ${formatMinutes(currentEtaMinutes)}.`;
      }
    }
  };

  const setDeliveryMethod = (method) => {
    selectedPaymentMethod = method;
    deliveryMethodButtons.forEach((button) => {
      button.classList.toggle('is-active', button.dataset.deliveryMethod === method);
    });
    if (deliveryCashButton) {
      deliveryCashButton.hidden = method !== 'cash';
    }
    if (deliveryCardButton) {
      deliveryCardButton.hidden = method !== 'card';
    }
  };

  const buildPlannerMap = () => {
    if (!deliveryPlanner || !deliveryMapElement || typeof L === 'undefined') {
      return;
    }
    if (plannerMap) {
      if (plannerLayer) {
        plannerLayer.clearLayers();
      }
      buildActiveDeliveryHubs();
      destinationMarker = L.circleMarker([deliveryDestination.lat, deliveryDestination.lng], {
        radius: 8,
        fillColor: '#ff7a1a',
        color: '#ffffff',
        weight: 3,
        fillOpacity: 1
      }).addTo(plannerLayer).bindTooltip('Your destination');
      activeDeliveryHubs.forEach((hub) => {
        const marker = L.marker([hub.lat, hub.lng]).addTo(plannerLayer);
        marker.bindTooltip(`${hub.name} (${hub.brand})`);
        marker.on('click', () => {
          selectedHub = hub;
          refreshDeliveryEstimate();
        });
      });
      plannerMap.invalidateSize();
      fitPlannerOverview();
      return;
    }

    buildActiveDeliveryHubs();
    plannerMap = L.map(deliveryMapElement, {
      scrollWheelZoom: false
    }).setView([deliveryDestination.lat, deliveryDestination.lng], 12);

    L.tileLayer(plannerTileConfig.url, {
      attribution: plannerTileConfig.attribution,
      subdomains: 'abcd',
      maxZoom: 20
    }).addTo(plannerMap);

    plannerLayer = L.layerGroup().addTo(plannerMap);
    destinationMarker = L.circleMarker([deliveryDestination.lat, deliveryDestination.lng], {
      radius: 8,
      fillColor: '#ff7a1a',
      color: '#ffffff',
      weight: 3,
      fillOpacity: 1
    }).addTo(plannerLayer).bindTooltip('Your destination');

    activeDeliveryHubs.forEach((hub) => {
      const marker = L.marker([hub.lat, hub.lng]).addTo(plannerLayer);
      marker.bindTooltip(`${hub.name} (${hub.brand})`);
      marker.on('click', () => {
        selectedHub = hub;
        refreshDeliveryEstimate();
      });
    });

    plannerMap.setView([deliveryDestination.lat, deliveryDestination.lng], 10);
  };

  const resolveDeliveryDestination = () => {
    if (!('geolocation' in navigator)) {
      updateDeliverySummary();
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (position) => {
        deliveryDestination = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          label: 'Your live location'
        };
        updateDeliverySummary();
        if (plannerMap && destinationMarker) {
          destinationMarker.setLatLng([deliveryDestination.lat, deliveryDestination.lng]);
          plannerMap.setView([deliveryDestination.lat, deliveryDestination.lng], 12);
        }
        if (selectedHub) {
          refreshDeliveryEstimate();
        }
      },
      () => {
        updateDeliverySummary();
      },
      { enableHighAccuracy: true, timeout: 7000 }
    );
  };

  const openDeliveryPlanner = () => {
    if (!canUseDelivery) {
      showCheckoutMessage(document.querySelector('[data-cart-checkout]'), premiumOnlyMessage);
      return;
    }
    if (!deliveryPlanner) {
      window.location.href = 'checkout.php';
      return;
    }
    if (cartModal) {
      cartModal.hidden = true;
    }
    deliveryPlanner.hidden = false;
    selectedHub = null;
    currentEtaMinutes = null;
    currentDistanceKm = null;
    plannerRequestToken += 1;
    drawRouteLine(null);
    setDeliveryMethod('');
    if (deliveryFeedbackNode) {
      deliveryFeedbackNode.textContent = '';
    }
    updateDeliverySummary();
    buildPlannerMap();
    resolveDeliveryDestination();
    window.setTimeout(() => {
      plannerMap?.invalidateSize();
    }, 80);
  };

  const setCartDragState = (isActive) => {
    floatingCartButtons.forEach((button) => {
      button.classList.toggle('is-drag-target', isActive);
    });
  };

  const cleanupDragPreview = () => {
    if (dragPreview && dragPreview.parentElement) {
      dragPreview.remove();
    }
    dragPreview = null;
  };

  const buildDragPreview = (product) => {
    cleanupDragPreview();

    const preview = document.createElement('div');
    preview.className = 'foovia-drag-preview';
    preview.innerHTML = `
      <img src="${product.image}" alt="${product.name}">
      <div class="foovia-drag-preview-copy">
        <strong>${product.name}</strong>
        <span>${formatUnitPrice(product.price, productUnit(product))}</span>
      </div>
    `;
    document.body.appendChild(preview);
    dragPreview = preview;
    return preview;
  };

  const showCheckoutMessage = (targetButton, message) => {
    const oldMessage = cartModal?.querySelector('.foovia-checkout-message');
    if (oldMessage) {
      oldMessage.remove();
    }

    if (!targetButton) {
      return;
    }

    const messageNode = document.createElement('div');
    messageNode.className = 'foovia-checkout-message';
    messageNode.textContent = message;
    targetButton.insertAdjacentElement('beforebegin', messageNode);
  };

  const showPagePremiumNotice = () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('delivery') !== 'upgrade') {
      return;
    }

    const target = document.querySelector('.foovia-recommend-section .container-lg')
      || document.querySelector('.foovia-catalog-section .container-lg')
      || document.querySelector('main')
      || document.body;
    const notice = document.createElement('div');
    notice.className = 'foovia-premium-notice';
    notice.textContent = premiumOnlyMessage;
    target.prepend(notice);
  };

  const showPremiumMessage = (button) => {
    if (!button) return;
    if (button.closest('[data-cart-modal]')) {
      showCheckoutMessage(button, premiumOnlyMessage);
      return;
    }
    if (typeof showReserveBubble === 'function') {
      showReserveBubble(button, premiumOnlyMessage);
    }
  };

  const applySubscriptionRules = () => {
    if (canUseDelivery) return;

    floatingCartButtons.forEach((button) => {
      button.hidden = true;
      button.setAttribute('aria-hidden', 'true');
    });

    document.querySelectorAll('[data-picker-confirm]').forEach((button) => {
      button.disabled = false;
      button.classList.add('foovia-premium-disabled');
      button.setAttribute('aria-disabled', 'true');
      button.textContent = 'Premium delivery only';
      button.title = premiumOnlyMessage;
    });

    document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
      button.disabled = false;
      button.classList.add('foovia-premium-disabled');
      button.setAttribute('aria-disabled', 'true');
      button.textContent = 'Premium delivery only';
      button.title = premiumOnlyMessage;
    });

    document.querySelectorAll('[data-cart-checkout]').forEach((button) => {
      button.disabled = false;
      button.classList.add('foovia-premium-disabled');
      button.setAttribute('aria-disabled', 'true');
      button.title = premiumOnlyMessage;
    });
  };

  const renderCart = () => {
    const totalItems = cart.length;
    const totalPrice = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);

    if (cartCount) cartCount.textContent = String(totalItems);
    if (cartTotal) cartTotal.textContent = `${formatPrice(totalPrice)} TND`;
    if (!cartItems) return;

    if (cart.length === 0) {
      cartItems.innerHTML = '<p class="text-muted mb-0">Your cart is empty.</p>';
      return;
    }

    cartItems.innerHTML = cart.map((item, index) => `
      <article class="foovia-cart-item">
        <img src="${item.image}" alt="${item.name}">
        <div>
          <h3>${item.name}</h3>
          <p>${formatQuantity(item.quantity, productUnit(item))} x ${formatUnitPrice(item.price, productUnit(item))}</p>
          <div class="foovia-cart-store">
            <div class="foovia-cart-store-line">
              <img src="${item.storeImage || item.image}" alt="${item.storeName}">
              <span>${item.storeName}</span>
            </div>
            <button type="button" class="foovia-cart-remove" data-cart-remove="${index}">Remove</button>
          </div>
        </div>
        <strong>${formatPrice(item.quantity * item.price)} TND</strong>
      </article>
    `).join('');

    cartItems.querySelectorAll('[data-cart-remove]').forEach((button) => {
      button.addEventListener('click', () => {
        const index = Number(button.dataset.cartRemove);
        if (!Number.isInteger(index)) return;
        cart.splice(index, 1);
        saveCart();
        renderCart();
      });
    });
  };

  const addItem = (item) => {
    const existing = cart.find((cartItem) => cartItem.id === item.id && cartItem.storeId === item.storeId);
    if (existing) {
      existing.quantity += item.quantity;
    } else {
      cart.push(item);
    }
    saveCart();
    renderCart();
  };

  const openPickerForProduct = (product, initialQuantity = '1') => {
    pendingProduct = product;

    if (pickerName) pickerName.textContent = pendingProduct.name;
    if (pickerStore) {
      pickerStore.innerHTML = (pendingProduct.stores || []).map((store, index) => `
        <label class="foovia-store-choice">
          <input
            type="radio"
            name="picker_store"
            value="${store.id}"
            data-store-name="${store.name}"
            data-store-image="${store.image}"
            ${index === 0 ? 'checked' : ''}
          >
          <span>${store.name}</span>
        </label>
      `).join('');
    }
    if (pickerQuantity) pickerQuantity.value = String(initialQuantity || '1');
    if (pickerPrice) pickerPrice.textContent = formatQuantityTotal(pendingProduct.price, pickerQuantity?.value, productUnit(pendingProduct));
    if (pickerReservationTotal) pickerReservationTotal.textContent = '0 reservations';
    if (picker) picker.hidden = false;
  };

  const updatePickerPrice = () => {
    if (!pendingProduct || !pickerPrice) {
      return;
    }

    pickerPrice.textContent = formatQuantityTotal(pendingProduct.price, pickerQuantity?.value, productUnit(pendingProduct));
  };

  const bindLiveQuantity = (input, callback) => {
    if (!input) {
      return;
    }

    const updateSoon = () => window.requestAnimationFrame(callback);
    ['input', 'change', 'keyup', 'paste', 'mouseup'].forEach((eventName) => {
      input.addEventListener(eventName, updateSoon);
    });
  };

  const updateDetailPrice = () => {
    const detailPrice = document.querySelector('[data-detail-price]');
    const detailTotal = document.querySelector('[data-detail-total]');
    const detailButton = document.querySelector('[data-add-to-cart]');
    const quantityInput = document.querySelector('[data-detail-quantity]');
    if (!detailPrice || !detailButton) {
      return;
    }

    const price = Number(detailButton.dataset.productPrice || 0);
    const unit = detailButton.dataset.productUnit || 'kg';
    detailPrice.textContent = formatUnitPrice(price, unit);
    if (detailTotal) {
      detailTotal.textContent = formatQuantityTotal(price, quantityInput?.value, unit);
    }
  };

  const readProductFromDataset = (source) => {
    let stores = [];
    try {
      const parsedStores = JSON.parse(source.dataset.productStores || '[]');
      stores = Array.isArray(parsedStores) ? parsedStores : [];
    } catch (error) {
      stores = [];
    }

    return {
      id: Number(source.dataset.productId),
      name: source.dataset.productName || 'Product',
      price: Number(source.dataset.productPrice || 0),
      unit: source.dataset.productUnit || 'kg',
      image: source.dataset.productImage || '',
      stores
    };
  };

  const getSelectedDetailStore = () => {
    const checkedStore = document.querySelector('[name="detail_store"]:checked');
    if (checkedStore) {
      return {
        id: Number(checkedStore.value || 0),
        name: checkedStore.dataset.storeName || checkedStore.parentElement?.textContent?.trim() || 'Store',
        image: checkedStore.dataset.storeImage || ''
      };
    }

    const storeSelect = document.querySelector('[data-detail-store]');
    const selectedStore = storeSelect?.selectedOptions[0];
    return {
      id: Number(storeSelect?.value || 0),
      name: selectedStore?.dataset.storeName || selectedStore?.textContent || 'Store',
      image: selectedStore?.dataset.storeImage || ''
    };
  };

  document.querySelectorAll('[data-cart-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!canUseDelivery) {
        return;
      }
      renderCart();
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-cart-close]').forEach((button) => {
    button.addEventListener('click', () => {
      if (cartModal) cartModal.hidden = true;
    });
  });

  document.querySelectorAll('[data-cart-checkout]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!canUseDelivery) {
        showCheckoutMessage(button, premiumOnlyMessage);
        return;
      }
      const totalItems = cart.length;
      const totalPrice = cart.reduce((sum, item) => sum + item.quantity * item.price, 0);
      if (totalItems === 0) {
        showCheckoutMessage(button, 'Your cart is empty. Add a product before checkout.');
        return;
      }

      showCheckoutMessage(button, `Plan delivery for ${totalItems} item(s) worth ${formatPrice(totalPrice)} TND...`);
      openDeliveryPlanner();
    });
  });

  document.querySelectorAll('[data-delivery-close]').forEach((button) => {
    button.addEventListener('click', () => {
      if (deliveryPlanner) {
        deliveryPlanner.hidden = true;
      }
    });
  });

  deliveryMethodButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (!canUseDelivery) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = premiumOnlyMessage;
        }
        return;
      }
      setDeliveryMethod(button.dataset.deliveryMethod || '');
    });
  });

  if (deliveryCashButton) {
      deliveryCashButton.addEventListener('click', async () => {
      if (!canUseDelivery) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = premiumOnlyMessage;
        }
        return;
      }
      if (!selectedHub) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = 'Choose a dispatch point on the map first.';
        }
        return;
      }
      if (!isSelectionDeliverable()) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = `Choose a market within ${maxDeliveryDistanceKm} km for delivery.`;
        }
        return;
      }
      const notificationsEnabled = await ensureNotificationPermission();
      const orderReference = `FV-${Date.now().toString().slice(-6)}`;
      saveDeliveryPlan({
        hub: selectedHub,
        destination: deliveryDestination,
        etaMinutes: currentEtaMinutes ?? estimateDeliveryMinutes(selectedHub, deliveryDestination),
        deliveryFee: currentDeliveryFee,
        weather: currentWeatherAdjustment,
        paymentMethod: 'cash'
      });
      localStorage.setItem(deliveryTrackerKey, JSON.stringify({
        reference: orderReference,
        hubName: selectedHub.name,
        destinationLabel: deliveryDestination.label,
        paymentMethod: 'cash',
        notificationsEnabled,
        etaMinutes: currentEtaMinutes ?? estimateDeliveryMinutes(selectedHub, deliveryDestination),
        startedAt: Date.now(),
        etaEndsAt: Date.now() + ((currentEtaMinutes ?? estimateDeliveryMinutes(selectedHub, deliveryDestination)) * 60 * 1000),
        status: 'in_transit'
      }));
      localStorage.removeItem(cartStorageKey);
      localStorage.removeItem(deliveryPlanKey);
      renderCart();
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = `Cash order confirmed. Estimated delivery time: ${formatMinutes(currentEtaMinutes ?? estimateDeliveryMinutes(selectedHub, deliveryDestination))}. Delivery fee: ${formatPrice(currentDeliveryFee)} TND.${notificationsEnabled ? ' Delivery notifications are enabled.' : ' Browser notifications are blocked, so only the in-site tracker will update.'}`;
      }
      window.setTimeout(() => {
        if (deliveryPlanner) {
          deliveryPlanner.hidden = true;
        }
      }, 1800);
    });
  }

  if (deliveryCardButton) {
    deliveryCardButton.addEventListener('click', () => {
      if (!canUseDelivery) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = premiumOnlyMessage;
        }
        return;
      }
      if (!selectedHub) {
      if (deliveryFeedbackNode) {
        deliveryFeedbackNode.textContent = 'Choose a dispatch point on the map first.';
      }
      return;
    }
      if (!isSelectionDeliverable()) {
        if (deliveryFeedbackNode) {
          deliveryFeedbackNode.textContent = `Choose a market within ${maxDeliveryDistanceKm} km for delivery.`;
        }
        return;
      }
      saveDeliveryPlan({
        hub: selectedHub,
        destination: deliveryDestination,
        etaMinutes: currentEtaMinutes ?? estimateDeliveryMinutes(selectedHub, deliveryDestination),
        deliveryFee: currentDeliveryFee,
        weather: currentWeatherAdjustment,
        paymentMethod: 'card'
      });
      window.location.href = 'checkout.php';
    });
  }

  document.querySelectorAll('[data-open-cart-picker]').forEach((button) => {
    button.addEventListener('click', () => {
      openPickerForProduct(readProductFromDataset(button));
    });
  });

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-recommend-add]');
    if (!button) {
      return;
    }

    openPickerForProduct(readProductFromDataset(button), button.dataset.recommendQuantity || '1');
  });

  document.querySelectorAll('[data-drag-product]').forEach((card) => {
    card.addEventListener('dragstart', (event) => {
      const product = readProductFromDataset(card);
      const preview = buildDragPreview(product);
      event.dataTransfer?.setData('application/json', JSON.stringify(product));
      event.dataTransfer?.setData('text/plain', String(product.id));
      event.dataTransfer.effectAllowed = 'copy';
      if (event.dataTransfer && preview) {
        event.dataTransfer.setDragImage(preview, 36, 24);
      }
      card.classList.add('is-dragging');
      setCartDragState(true);
    });

    card.addEventListener('dragend', () => {
      card.classList.remove('is-dragging');
      setCartDragState(false);
      cleanupDragPreview();
    });
  });

  floatingCartButtons.forEach((button) => {
    button.addEventListener('dragover', (event) => {
      event.preventDefault();
      event.dataTransfer.dropEffect = 'copy';
      setCartDragState(true);
    });

    button.addEventListener('dragenter', (event) => {
      event.preventDefault();
      setCartDragState(true);
    });

    button.addEventListener('dragleave', (event) => {
      if (event.relatedTarget && button.contains(event.relatedTarget)) {
        return;
      }
      setCartDragState(false);
    });

    button.addEventListener('drop', (event) => {
      event.preventDefault();
      setCartDragState(false);
      const payload = event.dataTransfer?.getData('application/json');
      if (!payload) {
        return;
      }

      try {
        const product = JSON.parse(payload);
        if (!product || !Array.isArray(product.stores)) {
          return;
        }
        openPickerForProduct(product);
      } catch (error) {
        // Ignore malformed drag payloads.
      }
    });
  });

  document.querySelectorAll('[data-picker-close]').forEach((button) => {
    button.addEventListener('click', () => {
      if (picker) picker.hidden = true;
    });
  });

  if (pickerQuantity) {
    bindLiveQuantity(pickerQuantity, updatePickerPrice);
  }

  document.querySelectorAll('[data-detail-quantity]').forEach((input) => {
    bindLiveQuantity(input, updateDetailPrice);
  });
  updateDetailPrice();

  document.querySelectorAll('[data-picker-confirm]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!canUseDelivery) {
        showPremiumMessage(button);
        return;
      }
      if (!pendingProduct || !pickerStore) return;
      const selectedStore = pickerStore.querySelector('[name="picker_store"]:checked');
      addItem({
        id: pendingProduct.id,
        name: pendingProduct.name,
        price: pendingProduct.price,
        unit: productUnit(pendingProduct),
        image: pendingProduct.image,
        quantity: parseQuantity(pickerQuantity?.value),
        storeId: Number(selectedStore?.value || 0),
        storeName: selectedStore?.dataset.storeName || selectedStore?.parentElement?.textContent?.trim() || 'Store',
        storeImage: selectedStore?.dataset.storeImage || ''
      });
      if (picker) picker.hidden = true;
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-picker-reserve]').forEach((button) => {
    button.addEventListener('click', async () => {
      if (!pendingProduct || !pickerStore) return;
      const selectedStore = pickerStore.querySelector('[name="picker_store"]:checked');
      const quantity = parseQuantity(pickerQuantity?.value);
      const formData = new FormData();
      formData.append('id_march', String(pendingProduct.id));
      formData.append('id_mag', selectedStore?.value || '0');
      formData.append('quantity_reservation', String(quantity));

      try {
        const response = await fetch(window.FOOVIA_RESERVATION_ENDPOINT || `${window.FOOVIA_APP_BASE || ''}/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=reserve`, {
          method: 'POST',
          body: formData
        });
        if (!response.ok) throw new Error('Reservation failed');
        showReserveBubble(button, 'Reservation complete.');
        if (pickerReservationTotal) {
          const current = Number.parseInt(pickerReservationTotal.textContent, 10) || 0;
          pickerReservationTotal.textContent = `${current + quantity} reservations`;
        }
      } catch (error) {
        showReserveBubble(button, 'Reservation could not be saved.');
      }
    });
  });

  document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
    button.addEventListener('click', () => {
      if (!canUseDelivery) {
        showPremiumMessage(button);
        return;
      }
      const quantityInput = document.querySelector('[data-detail-quantity]');
      const selectedStore = getSelectedDetailStore();

      addItem({
        id: Number(button.dataset.productId),
        name: button.dataset.productName || 'Product',
        price: Number(button.dataset.productPrice || 0),
        unit: button.dataset.productUnit || 'kg',
        image: button.dataset.productImage || '',
        quantity: parseQuantity(quantityInput?.value),
        storeId: selectedStore.id,
        storeName: selectedStore.name,
        storeImage: selectedStore.image
      });
      if (cartModal) cartModal.hidden = false;
    });
  });

  document.querySelectorAll('[data-reserve-product]').forEach((button) => {
    button.addEventListener('click', async () => {
      const quantityInput = document.querySelector('[data-detail-quantity]');
      const selectedStore = getSelectedDetailStore();
      const formData = new FormData();
      formData.append('id_march', button.dataset.productId || '0');
      formData.append('id_mag', String(selectedStore.id));
      formData.append('quantity_reservation', String(parseQuantity(quantityInput?.value)));

      try {
        const response = await fetch(window.FOOVIA_RESERVATION_ENDPOINT || `${window.FOOVIA_APP_BASE || ''}/MVC/Controller/MARKETPLACE_MODULE/Marchandise_Controller.php?action=reserve`, {
          method: 'POST',
          body: formData
        });
        if (!response.ok) throw new Error('Reservation failed');
        showReserveBubble(button, 'Reservation complete.');
        updateReservationTotal(parseQuantity(quantityInput?.value));
      } catch (error) {
        showReserveBubble(button, 'Reservation could not be saved.');
      }
    });
  });

  const showReserveBubble = (target, message) => {
    const existing = target.parentElement.querySelector('.foovia-reserve-bubble');
    if (existing) existing.remove();
    const bubble = document.createElement('span');
    bubble.className = 'foovia-reserve-bubble';
    bubble.textContent = message;
    target.insertAdjacentElement('afterend', bubble);
    window.setTimeout(() => bubble.remove(), 2600);
  };

  const updateReservationTotal = (quantity) => {
    const totalNode = document.querySelector('[data-reservation-total]');
    if (!totalNode) return;
    const current = Number.parseInt(totalNode.textContent, 10) || 0;
    totalNode.textContent = `${current + quantity} reservations`;
  };

  showPagePremiumNotice();
  applySubscriptionRules();
  renderCart();
});
