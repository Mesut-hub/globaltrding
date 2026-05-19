function initDocDropdownToolbar() {
  const toolbar = document.querySelector('[data-docdd-toolbar]');
  if (!toolbar) return;

  const toggleBtn = toolbar.querySelector('[data-docdd-toggle]');
  const langSelect = toolbar.querySelector('[data-docdd-lang]');
  const searchInput = toolbar.querySelector('[data-docdd-search]');

  // The document items are <details class="gt-docdd__item"> ... and rows have [data-docdd-row]
  const scope = document.querySelector('[data-docdd-scope]') || document;
  const detailItems = Array.from(scope.querySelectorAll('details.gt-docdd__item'));
  const rows = Array.from(scope.querySelectorAll('[data-docdd-row]'));

  if (!detailItems.length || !rows.length) return;

  const normalize = (s) => (s || '').toString().trim().toLowerCase();

  function setToggleLabel() {
    if (!toggleBtn) return;
    const allOpen = detailItems.every(d => d.open);
    toggleBtn.textContent = allOpen ? 'Collapse all' : 'Expand all';
    toggleBtn.setAttribute('aria-pressed', allOpen ? 'true' : 'false');
  }

  function applyFilters() {
    const q = normalize(searchInput?.value);
    const lang = normalize(langSelect?.value);

    rows.forEach(row => {
      const title = normalize(row.getAttribute('data-docdd-title'));
      const rowLang = normalize(row.getAttribute('data-docdd-lang'));

      const okQuery = !q || title.includes(q);
      const okLang = !lang || rowLang === lang;

      row.style.display = (okQuery && okLang) ? '' : 'none';
    });
  }

  toggleBtn?.addEventListener('click', () => {
    const allOpen = detailItems.every(d => d.open);
    detailItems.forEach(d => (d.open = !allOpen));
    setToggleLabel();
  });

  detailItems.forEach(d => d.addEventListener('toggle', setToggleLabel));

  langSelect?.addEventListener('change', applyFilters);
  searchInput?.addEventListener('input', applyFilters);

  // Initialize label + filtering once
  setToggleLabel();
  applyFilters();
}

document.addEventListener('DOMContentLoaded', initDocDropdownToolbar);
document.addEventListener('turbo:load', initDocDropdownToolbar); // harmless if Turbo not used

export { initDocDropdownToolbar };