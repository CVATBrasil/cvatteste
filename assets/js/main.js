/**
 * CVAT Brasil — main.js
 * Loader de componentes via fetch, header behavior, hamburguer, dropdowns,
 * scroll spy, animações de entrada, newsletter footer.
 */

/* ── 1. CARREGAMENTO DE COMPONENTES ─────────────────────────── */

/**
 * Resolve o caminho do componente relativo à raiz do site,
 * independente da profundidade da página atual ou do subdiretório de hospedagem.
 * Usa o src do próprio script (sempre URL absoluta) para calcular a raiz.
 */
function resolveComponentPath(component) {
  const scripts = Array.from(document.querySelectorAll('script[src]'));
  const mainScript = scripts.find(s => s.getAttribute('src').includes('main.js'));
  if (mainScript) {
    /* mainScript.src é sempre URL absoluta; main.js fica em <root>/assets/js/main.js */
    const base = mainScript.src.replace(/assets\/js\/main\.js(\?.*)?$/, '');
    return `${base}components/${component}`;
  }
  /* Fallback: path relativo pela profundidade */
  const depth = (window.location.pathname.match(/\//g) || []).length - 1;
  const prefix = depth > 0 ? '../'.repeat(depth) : './';
  return `${prefix}components/${component}`;
}

/**
 * Carrega um fragmento HTML via fetch e injeta no elemento alvo.
 * Executa scripts inline contidos no fragmento após a injeção.
 */
async function loadComponent(selector, component) {
  const target = document.querySelector(selector);
  if (!target) return;

  try {
    const path = resolveComponentPath(component);
    const res  = await fetch(path);
    if (!res.ok) throw new Error(`HTTP ${res.status} ao carregar ${path}`);
    const html = await res.text();
    target.innerHTML = html;
    runInlineScripts(target);
  } catch (err) {
    console.warn(`[CVAT] Componente não carregado: ${component}`, err);
  }
}

/** Re-executa <script> tags injetadas via innerHTML (browsers não as executam). */
function runInlineScripts(container) {
  container.querySelectorAll('script').forEach(old => {
    const fresh = document.createElement('script');
    [...old.attributes].forEach(a => fresh.setAttribute(a.name, a.value));
    fresh.textContent = old.textContent;
    old.replaceWith(fresh);
  });
}

/* ── 2. HEADER: SCROLL, HAMBURGUER, DROPDOWNS ───────────────── */

function initHeader() {
  const header    = document.getElementById('site-header');
  const hamburger = document.getElementById('hamburger');
  const nav       = document.getElementById('header-nav');
  const overlay   = document.getElementById('nav-overlay');

  if (!header) return;

  /* --- Sombra no scroll --- */
  const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 10);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* --- Hamburguer --- */
  if (hamburger && nav && overlay) {
    hamburger.addEventListener('click', () => toggleMobileMenu());
    overlay.addEventListener('click', () => closeMobileMenu());

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') closeMobileMenu();
    });
  }

  /* --- Dropdowns desktop/mobile --- */
  const toggles = header.querySelectorAll('.nav-toggle');

  toggles.forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const targetId = btn.getAttribute('aria-controls');
      const dropdown = document.getElementById(targetId);
      const isOpen   = btn.getAttribute('aria-expanded') === 'true';

      closeAllDropdowns(btn);

      if (!isOpen && dropdown) {
        btn.setAttribute('aria-expanded', 'true');
        dropdown.classList.add('open');
      }
    });
  });

  document.addEventListener('click', () => closeAllDropdowns());

  /* Foco fora fecha dropdown */
  document.addEventListener('focusin', e => {
    if (!header.contains(e.target)) closeAllDropdowns();
  });

  /* --- Link ativo --- */
  markActiveNavLink();
}

function toggleMobileMenu() {
  const hamburger = document.getElementById('hamburger');
  const nav       = document.getElementById('header-nav');
  const overlay   = document.getElementById('nav-overlay');
  const isOpen    = hamburger.getAttribute('aria-expanded') === 'true';

  if (isOpen) {
    closeMobileMenu();
  } else {
    hamburger.setAttribute('aria-expanded', 'true');
    nav.classList.add('open');
    overlay.classList.add('visible');
    overlay.removeAttribute('aria-hidden');
    document.body.style.overflow = 'hidden';
    nav.querySelector('a, button')?.focus();
  }
}

function closeMobileMenu() {
  const hamburger = document.getElementById('hamburger');
  const nav       = document.getElementById('header-nav');
  const overlay   = document.getElementById('nav-overlay');
  if (!hamburger) return;

  hamburger.setAttribute('aria-expanded', 'false');
  nav?.classList.remove('open');
  overlay?.classList.remove('visible');
  overlay?.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

function closeAllDropdowns(except = null) {
  document.querySelectorAll('.nav-toggle').forEach(btn => {
    if (btn === except) return;
    btn.setAttribute('aria-expanded', 'false');
    const id = btn.getAttribute('aria-controls');
    document.getElementById(id)?.classList.remove('open');
  });
}

function markActiveNavLink() {
  const path = window.location.pathname;
  document.querySelectorAll('#header-nav a').forEach(link => {
    const href = link.getAttribute('href') || '';
    const clean = href.replace(/^\.\.\//, '/').replace(/^\.\//, '/');
    if (path.endsWith(clean) || (clean !== '/' && path.includes(clean.replace(/\.html$/, '')))) {
      link.classList.add('active');
      link.setAttribute('aria-current', 'page');
    }
  });
}

/* ── 3. PAGE LOADER ──────────────────────────────────────────── */

function initPageLoader() {
  const loader = document.getElementById('page-loader');
  if (!loader) return;

  function dismissLoader() {
    loader.classList.add('fade-out');
    setTimeout(() => loader.remove(), 350);
  }

  /* Dispara no load ou em até 1.5s — o que vier primeiro */
  window.addEventListener('load', dismissLoader);
  setTimeout(dismissLoader, 1500);
}

/* ── 4. ANIMAÇÕES DE ENTRADA (Intersection Observer) ────────── */

function initRevealAnimations() {
  if (!('IntersectionObserver' in window)) return;

  const style = document.createElement('style');
  style.textContent = `
    [data-reveal] {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity .5s ease, transform .5s ease;
    }
    [data-reveal].revealed {
      opacity: 1;
      transform: translateY(0);
    }
    [data-reveal-delay="1"] { transition-delay: .1s; }
    [data-reveal-delay="2"] { transition-delay: .2s; }
    [data-reveal-delay="3"] { transition-delay: .3s; }
    [data-reveal-delay="4"] { transition-delay: .4s; }
    [data-reveal-delay="5"] { transition-delay: .5s; }
  `;
  document.head.appendChild(style);

  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
  );

  document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
}

/* ── 5. SMOOTH SCROLL PARA ÂNCORAS ──────────────────────────── */

function initSmoothScroll() {
  document.addEventListener('click', e => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;

    const id     = link.getAttribute('href').slice(1);
    const target = document.getElementById(id);
    if (!target) return;

    e.preventDefault();
    const headerHeight = parseInt(
      getComputedStyle(document.documentElement).getPropertyValue('--header-height') || '68',
      10
    );
    const top = target.getBoundingClientRect().top + window.scrollY - headerHeight - 8;
    window.scrollTo({ top, behavior: 'smooth' });
    target.setAttribute('tabindex', '-1');
    target.focus({ preventScroll: true });
  });
}

/* ── 6. TOAST HELPER ─────────────────────────────────────────── */

/**
 * Exibe uma mensagem de toast.
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {number} duration ms
 */
function showToast(message, type = 'info', duration = 4000) {
  let toast = document.getElementById('cvat-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id   = 'cvat-toast';
    toast.className = 'toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
  }

  toast.className   = `toast toast-${type}`;
  toast.textContent = message;
  toast.classList.add('show');

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), duration);
}

/* ── 7. NEWSLETTER DO FOOTER ─────────────────────────────────── */

function initFooterNewsletter() {
  const form    = document.getElementById('footer-newsletter-form');
  const input   = document.getElementById('footer-email');
  const errorEl = document.getElementById('footer-email-error');
  const successEl = document.getElementById('footer-email-success');
  if (!form) return;

  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  input?.addEventListener('input', () => {
    if (EMAIL_RE.test(input.value.trim())) {
      input.classList.remove('error');
      if (errorEl) errorEl.textContent = '';
    }
  });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const email = input?.value.trim() ?? '';

    if (!EMAIL_RE.test(email)) {
      input?.classList.add('error');
      if (errorEl) errorEl.textContent = 'Por favor, insira um e-mail válido.';
      input?.focus();
      return;
    }

    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn?.textContent;
    if (btn) { btn.disabled = true; btn.textContent = 'Enviando…'; }

    try {
      const res = await fetch(form.action, {
        method:  'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body:    JSON.stringify({ email }),
      });

      if (res.ok) {
        form.querySelector('.footer-newsletter-field').style.display = 'none';
        successEl?.classList.remove('hidden');
      } else {
        throw new Error('Resposta não-ok do servidor');
      }
    } catch {
      if (errorEl) errorEl.textContent = 'Erro ao enviar. Tente novamente em instantes.';
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = originalText; }
    }
  });
}

/* ── 8. UTILITÁRIO: DEBOUNCE ─────────────────────────────────── */

function debounce(fn, ms = 150) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), ms);
  };
}

/* ── 9. BOOTSTRAP ────────────────────────────────────────────── */

async function bootstrap() {
  /* Carrega header e footer nos placeholders */
  await Promise.all([
    loadComponent('#header-placeholder', 'header.html'),
    loadComponent('#footer-placeholder', 'footer.html'),
  ]);

  /* Inicia comportamentos após componentes carregados */
  initHeader();
  initFooterNewsletter();
  initPageLoader();
  initRevealAnimations();
  initSmoothScroll();
}

/* Aguarda o DOM estar pronto antes de executar */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootstrap);
} else {
  bootstrap();
}

/* Exporta helpers para uso em outros scripts */
window.CVAT = { showToast, debounce, closeMobileMenu };
