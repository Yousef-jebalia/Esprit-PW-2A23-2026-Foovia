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
    // If a page opts out by setting `window.FOOVIA_DISABLE_SIDEBAR_CSS = true`,
    // we inject a minimal, scoped stylesheet instead of the full foovia.css
    const hasFooviaLink = !!document.querySelector('link[href*="foovia.css"]');
    const disableFullCss = !!window.FOOVIA_DISABLE_SIDEBAR_CSS;
    if (!hasFooviaLink && !disableFullCss) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = basePath + 'foovia.css';
      document.head.appendChild(link);
    } else if (!hasFooviaLink && disableFullCss) {
      // Inject a fuller, self-contained sidebar stylesheet scoped to avoid
      // bringing in the entire `foovia.css`. This reproduces the panel's
      // layout and visual details while minimizing global impact.
      if (!document.querySelector('style[data-foovia-sidebar]')) {
        const style = document.createElement('style');
        style.setAttribute('data-foovia-sidebar', '');
        style.textContent = `
          /* Scoped Foovia sidebar styles (safe for host pages) */
          body.nav-sidebar-open{overflow:hidden}
          body.nav-sidebar-open > :not(nav):not(.nav-sidebar-panel):not(.nav-sidebar-backdrop){filter:blur(6px) saturate(.85);pointer-events:none;user-select:none}
          .nav-sidebar-toggle{width:54px;height:46px;border-radius:14px;border:1.5px solid rgba(0,0,0,.08);background:rgba(255,255,255,.35);display:inline-flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;cursor:pointer;transition:transform .15s ease,background-color .2s ease,border-color .2s ease;flex-shrink:0}
          .nav-sidebar-toggle span{width:24px;height:3px;border-radius:999px;background:#111008;transition:background-color .2s ease,transform .2s ease}
          .nav-sidebar-backdrop{position:fixed;inset:0;z-index:1090;background:rgba(17,16,8,.48);opacity:0;transition:opacity .25s ease}
          .nav-sidebar-backdrop:not([hidden]){display:block}
          body.nav-sidebar-open .nav-sidebar-backdrop{opacity:1}
          .nav-sidebar-panel{position:fixed;top:0;left:0;z-index:1100;width:min(340px,calc(100vw - 36px));height:100vh;background:#ffffff;border-right:1px solid rgba(0,0,0,.06);border-radius:0 24px 24px 0;box-shadow:0 20px 60px rgba(0,0,0,.24);transform:translateX(-102%);opacity:0;transition:transform .42s cubic-bezier(.22,1,.36,1),opacity .22s ease;padding:24px 20px;display:flex;flex-direction:column;gap:20px;backdrop-filter:blur(12px)}
          .nav-sidebar-panel[hidden]{display:none !important}
          .nav-sidebar-panel.is-open{transform:translateX(0);opacity:1;animation:navSidebarFloatIn .42s cubic-bezier(.22,1,.36,1)}
          @keyframes navSidebarFloatIn{0%{transform:translateX(-106%);opacity:0}70%{transform:translateX(4px);opacity:1}100%{transform:translateX(0);opacity:1}}
          .nav-sidebar-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
          .nav-sidebar-kicker{font-family:DM Sans, sans-serif;font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:#6b7280;margin-bottom:8px}
          .nav-sidebar-header h2{font-family:Boldonse, sans-serif;font-size:1.4rem;line-height:1.1}
          .nav-sidebar-close{width:38px;height:38px;border-radius:50%;border:1.5px solid rgba(0,0,0,.06);background:transparent;color:#111008;font-size:1.5rem;line-height:1;cursor:pointer}
          .nav-sidebar-nav ul{list-style:none;display:flex;flex-direction:column;gap:10px;padding-left:0}
          .nav-sidebar-link{display:flex;align-items:center;padding:14px 16px;border-radius:16px;font-family:Syne, sans-serif;font-weight:700;font-size:.95rem;color:#111008;text-decoration:none;background:rgba(255,255,255,.55);border:1px solid transparent;transition:transform .15s ease,background-color .2s ease,border-color .2s ease}
          .nav-sidebar-link:hover,.nav-sidebar-link:focus-visible,.nav-sidebar-link.is-active{background:rgba(75,174,82,.12);border-color:rgba(75,174,82,.22);transform:translateX(2px)}
          .nav-sidebar-link[aria-current="page"]{background:#4BAA52;color:#fff}
          @media (max-width:1100px){.nav-links{display:none}}
          @media (max-width:760px){nav{padding:16px 18px;gap:12px}.nav-logo{font-size:1.2rem}.nav-sidebar-panel{width:min(86vw,340px);padding:22px 16px}}
        `;
        document.head.appendChild(style);
      }
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
