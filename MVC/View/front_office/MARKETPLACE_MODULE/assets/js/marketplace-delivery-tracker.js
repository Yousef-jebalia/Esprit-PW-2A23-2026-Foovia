document.addEventListener('DOMContentLoaded', () => {
  const storageKey = 'fooviaDeliveryTracker';
  const noticeButton = document.querySelector('[data-delivery-notice]');
  const noticeTitle = document.querySelector('[data-delivery-notice-title]');
  const noticeText = document.querySelector('[data-delivery-notice-text]');
  const modal = document.querySelector('[data-delivery-tracker-modal]');
  const modalTitle = document.querySelector('[data-delivery-tracker-title]');
  const modalCopy = document.querySelector('[data-delivery-tracker-copy]');
  const modalCountdown = document.querySelector('[data-delivery-tracker-countdown]');
  const modalReference = document.querySelector('[data-delivery-tracker-reference]');
  const modalHub = document.querySelector('[data-delivery-tracker-hub]');
  const modalDestination = document.querySelector('[data-delivery-tracker-destination]');
  const modalStatus = document.querySelector('[data-delivery-tracker-status]');
  const closeButtons = Array.from(document.querySelectorAll('[data-delivery-tracker-close]'));
  const hasUi = Boolean(noticeButton && modal);

  let tracker = null;
  let intervalId = null;
  let isSendingNotification = false;
  let deliveredCleanupId = null;
  const serviceWorkerVersion = 'foovia-sw-v2';
  let serviceWorkerRegistration = null;

  const serviceWorkerUrl = '/integration%20foovia/MVC/View/front_office/MARKETPLACE_MODULE/foovia-sw.js';

  const registerNotificationWorker = async () => {
    if (!('serviceWorker' in navigator)) {
      return null;
    }

    if (serviceWorkerRegistration) {
      return serviceWorkerRegistration;
    }

    try {
      serviceWorkerRegistration = await navigator.serviceWorker.register(`${serviceWorkerUrl}?v=${serviceWorkerVersion}`);
      return serviceWorkerRegistration;
    } catch (error) {
      return null;
    }
  };

  const loadTracker = () => {
    try {
      const parsed = JSON.parse(localStorage.getItem(storageKey) || 'null');
      return parsed && typeof parsed === 'object' ? parsed : null;
    } catch (error) {
      return null;
    }
  };

  const saveTracker = (value) => {
    localStorage.setItem(storageKey, JSON.stringify(value));
  };

  const clearTracker = () => {
    localStorage.removeItem(storageKey);
    tracker = null;
    if (noticeButton) {
      noticeButton.hidden = true;
    }
    if (modal) {
      modal.hidden = true;
    }
    if (deliveredCleanupId) {
      window.clearTimeout(deliveredCleanupId);
      deliveredCleanupId = null;
    }
  };

  const formatRemaining = (milliseconds) => {
    const seconds = Math.max(0, Math.ceil(milliseconds / 1000));
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
  };

  const syncDeliveredState = () => {
    if (!tracker) {
      return;
    }

    if (tracker.status !== 'delivered' && Date.now() >= Number(tracker.etaEndsAt || 0)) {
      tracker.status = 'delivered';
      tracker.deliveredAt = Date.now();
      saveTracker(tracker);
    }
  };

  const trySendDeliveredNotification = async () => {
    if (!tracker || tracker.status !== 'delivered' || tracker.notificationSentAt || tracker.notificationAttemptedAt || isSendingNotification) {
      return;
    }

    if (!('Notification' in window)) {
      tracker.notificationAttemptedAt = Date.now();
      tracker.notificationError = 'Browser notifications are not supported here.';
      saveTracker(tracker);
      return;
    }

    if (Notification.permission !== 'granted') {
      tracker.notificationAttemptedAt = Date.now();
      tracker.notificationError = 'Browser notifications are not enabled.';
      saveTracker(tracker);
      return;
    }

    isSendingNotification = true;

    try {
      const registration = await registerNotificationWorker();
      const notificationPayload = {
        body: `${tracker.reference} from ${tracker.hubName} has arrived.`,
        tag: `foovia-delivery-${tracker.reference}`,
        renotify: true,
        data: {
          url: window.location.href
        }
      };

      if (registration && typeof registration.showNotification === 'function') {
        await registration.showNotification('Foovia delivery update', notificationPayload);
      } else {
        const notification = new Notification('Foovia delivery update', notificationPayload);
        notification.onclick = () => {
          window.focus();
          if (modal) {
            modal.hidden = false;
          }
        };
      }

      tracker.notificationAttemptedAt = Date.now();
      tracker.notificationSentAt = Date.now();
      saveTracker(tracker);
    } catch (error) {
      tracker.notificationAttemptedAt = Date.now();
      tracker.notificationError = error.message || 'Notification could not be sent.';
      saveTracker(tracker);
    } finally {
      isSendingNotification = false;
    }
  };

  const scheduleDeliveredCleanup = () => {
    if (!tracker || tracker.status !== 'delivered' || deliveredCleanupId) {
      return;
    }

    deliveredCleanupId = window.setTimeout(() => {
      clearTracker();
    }, 5000);
  };

  const render = () => {
    tracker = loadTracker();
    if (!tracker) {
      if (noticeButton) {
        noticeButton.hidden = true;
      }
      if (modal) {
        modal.hidden = true;
      }
      return;
    }

    syncDeliveredState();

    const isDelivered = tracker.status === 'delivered';
    if (noticeButton) {
      noticeButton.hidden = false;
      noticeButton.classList.toggle('is-delivered', isDelivered);
    }

    if (noticeTitle) {
      noticeTitle.textContent = isDelivered ? 'Order delivered' : 'Delivery in progress';
    }
    if (noticeText) {
      noticeText.textContent = isDelivered
        ? `${tracker.reference} has arrived. Tap to view the order summary.`
        : `${tracker.reference} arrives in about ${formatRemaining(Number(tracker.etaEndsAt || 0) - Date.now())}.`;
    }

    if (modalTitle) {
      modalTitle.textContent = isDelivered ? 'Your order has arrived' : 'Your order is on the way';
    }
    if (modalCopy) {
      modalCopy.textContent = isDelivered
        ? `Foovia delivered your order from ${tracker.hubName}.`
        : `Foovia is dispatching your order from ${tracker.hubName} to ${tracker.destinationLabel}.`;
    }
    if (modalCountdown) {
      modalCountdown.textContent = isDelivered ? 'Delivered' : formatRemaining(Number(tracker.etaEndsAt || 0) - Date.now());
    }
    if (modalReference) {
      modalReference.textContent = tracker.reference || 'FV-000000';
    }
    if (modalHub) {
      modalHub.textContent = tracker.hubName || 'Selected store';
    }
    if (modalDestination) {
      modalDestination.textContent = tracker.destinationLabel || 'Your location';
    }
    if (modalStatus) {
      modalStatus.textContent = isDelivered ? 'Delivered' : 'In transit';
    }

    if (isDelivered) {
      trySendDeliveredNotification();
      scheduleDeliveredCleanup();
    }
  };

  const openModal = () => {
    if (!modal) {
      return;
    }
    render();
    modal.hidden = false;
  };

  const closeModal = () => {
    if (modal) {
      modal.hidden = true;
    }
  };

  if (hasUi) {
    noticeButton.addEventListener('click', openModal);
    closeButtons.forEach((button) => button.addEventListener('click', closeModal));
    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModal();
      }
    });
  }

  if ('serviceWorker' in navigator) {
    registerNotificationWorker();
    navigator.serviceWorker.addEventListener('message', (event) => {
      if (event.data?.type === 'foovia-delivery-notification-click') {
        openModal();
      }
    });
  }

  render();
  intervalId = window.setInterval(render, 1000);
  window.addEventListener('beforeunload', () => {
    if (intervalId) {
      window.clearInterval(intervalId);
    }
  });
});
