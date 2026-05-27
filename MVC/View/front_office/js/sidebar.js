(function() {
  const initSidebar = () => {
    // 1. Find the toggle button
    const openButton = document.querySelector('.nav-sidebar-toggle');
    if (!openButton) {
      return;
    }

    // Guard against multiple initializations
    if (window.__fooviaSidebarInitialized) {
      return;
    }
    window.__fooviaSidebarInitialized = true;

    // 2. Determine base path to the front_office root dynamically
    const path = window.location.pathname;
    const lowerPath = path.toLowerCase();
    const foIndex = lowerPath.indexOf('/front_office/');
    let basePath = '';
    if (foIndex !== -1) {
      basePath = path.substring(0, foIndex + '/front_office/'.length);
    } else {
      // Fallback
      basePath = '/';
    }

    // 3. Inject CSS if it is not present
    if (!document.querySelector('link[href*="foovia.css"]')) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = basePath + 'foovia.css';
      document.head.appendChild(link);
    }

    // 4. Create backdrop and panel if they do not exist
    let backdrop = document.querySelector('.nav-sidebar-backdrop');
    if (!backdrop) {
      backdrop = document.createElement('div');
      backdrop.className = 'nav-sidebar-backdrop';
      backdrop.setAttribute('data-sidebar-close', '');
      backdrop.hidden = true;
      document.body.appendChild(backdrop);
    }

    let panel = document.getElementById('navSidebar');
    if (!panel) {
      panel = document.createElement('aside');
      panel.className = 'nav-sidebar-panel';
      panel.id = 'navSidebar';
      panel.setAttribute('aria-hidden', 'true');
      panel.hidden = true;

      // Define navigation items with relative links from basePath
      const navItems = [
        { label: 'Home', href: 'foovia.php' },
        { label: 'Recipes', href: 'menu_module/recipe_page.php' },
        { label: 'Tracking', href: 'TRACK_MODULE/tracking.php' },
        { label: 'Sport', href: 'SPORT_MOULE/Exercice.php' },
        { label: 'Marketplace', href: 'marketplace-gateway.php' },
        { label: 'Support', href: 'SUPPORT_MODULE/support_rec_page.php' }
      ];

      const currentFilename = path.split('/').pop() || 'foovia.php';

      // Generate link items
      let linksHtml = '';
      navItems.forEach(item => {
        const fullHref = basePath + item.href;
        const itemFilename = item.href.split('/').pop();
        const isActive = currentFilename.toLowerCase() === itemFilename.toLowerCase();
        const activeClass = isActive ? ' is-active' : '';
        const ariaCurrent = isActive ? ' aria-current="page"' : '';
        
        linksHtml += `
          <li>
            <a href="${fullHref}" class="nav-sidebar-link${activeClass}"${ariaCurrent}>
              ${item.label}
            </a>
          </li>
        `;
      });

      panel.innerHTML = `
        <div class="nav-sidebar-header">
          <div>
            <p class="nav-sidebar-kicker">Navigate</p>
            <h2>Page list</h2>
          </div>
          <button class="nav-sidebar-close" type="button" aria-label="Close page list" data-sidebar-close>&times;</button>
        </div>
        <div class="nav-sidebar-nav" aria-label="Page navigation">
          <ul>
            ${linksHtml}
          </ul>
        </div>
      `;

      document.body.appendChild(panel);
    }

    // 5. Setup event listeners
    const body = document.body;
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    const setOpen = (open) => {
      body.classList.toggle('nav-sidebar-open', open);
      panel.classList.toggle('is-open', open);
      panel.hidden = !open;
      backdrop.hidden = !open;
      openButton.setAttribute('aria-expanded', String(open));
      panel.setAttribute('aria-hidden', String(!open));
    };

    openButton.addEventListener('click', () => {
      setOpen(!body.classList.contains('nav-sidebar-open'));
    });

    closeButtons.forEach((button) => {
      button.addEventListener('click', () => setOpen(false));
    });

    backdrop.addEventListener('click', () => setOpen(false));

    panel.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });
  };

  // Run when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebar);
  } else {
    initSidebar();
  }
})();
