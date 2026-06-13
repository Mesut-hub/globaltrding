import Chart from 'chart.js/auto';

function pickLocaleName(name, locale) {
  if (!name) return '';
  if (typeof name === 'string') return name;
  return name[locale] || name.en || name.tr || '';
}

function fmt(v) {
  if (v === null || v === undefined) return '—';
  if (typeof v !== 'number') return String(v);
  return v.toLocaleString(undefined, { maximumFractionDigits: 4 });
}

document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-market-root]');
  if (!root) return;

  const locale = root.getAttribute('data-locale') || 'en';
  const defaultPeriod = '3m';

  const instrumentSelect = document.getElementById('instrumentSelect');
  const periodButtons = document.querySelectorAll('.periodBtn');
  const customRange = document.getElementById('customRange');
  const fromDate = document.getElementById('fromDate');
  const toDate = document.getElementById('toDate');
  const applyCustom = document.getElementById('applyCustom');

  const rangeText = document.getElementById('rangeText');
  const titleText = document.getElementById('titleText');
  const latestText = document.getElementById('latestText');
  const emptyHint = document.getElementById('emptyHint');

  const latestTable = document.getElementById('latestTable');
  const beltSlugs = root.getAttribute('data-belt-slugs') || '';

  let currentPeriod = defaultPeriod;
  let chart;

  function setActivePeriodBtn() {
    periodButtons.forEach((btn) => {
      const isActive = btn.dataset.period === currentPeriod;
      btn.classList.toggle('bg-slate-900', isActive);
      btn.classList.toggle('text-white', isActive);
    });

    customRange?.classList.toggle('hidden', currentPeriod !== 'custom');
  }

  async function fetchJson(url) {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  }

  async function loadLatestTable() {
    if (!latestTable || !beltSlugs) return;

    try {
      const url = `/${locale}/market/data?instruments=${encodeURIComponent(beltSlugs)}`;
      const json = await fetchJson(url);

      Object.keys(json).forEach((slug) => {
        const row = latestTable.querySelector(`[data-latest-row="${slug}"]`);
        if (!row) return;

        row.querySelector('[data-latest-value]').textContent = fmt(json[slug]?.value);
        row.querySelector('[data-latest-unit]').textContent = json[slug]?.unit || '';
        row.querySelector('[data-latest-date]').textContent = json[slug]?.date || '—';
      });
    } catch (e) {
      // silent; page still usable
    }
  }

  async function loadChart() {
    const instrument = instrumentSelect.value;

    const params = new URLSearchParams();
    params.set('instrument', instrument);
    params.set('period', currentPeriod);

    if (currentPeriod === 'custom') {
      if (fromDate?.value) params.set('from', fromDate.value);
      if (toDate?.value) params.set('to', toDate.value);
    }

    const url = `/${locale}/market/data?${params.toString()}`;
    const json = await fetchJson(url);

    const points = json.points || [];
    const labels = points.map((p) => p.date);
    const values = points.map((p) => p.value);

    rangeText.textContent = `${json.range.from} → ${json.range.to}`;

    const label = pickLocaleName(json.instrument?.name, locale) || json.instrument?.slug || 'Market';
    titleText.textContent = label;

    const lastVal = values.length ? values[values.length - 1] : null;
    latestText.textContent = fmt(lastVal);

    emptyHint.classList.toggle('hidden', values.length > 0);

    const ctx = document.getElementById('marketChart');
    if (!chart) {
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label,
              data: values,
              borderWidth: 2,
              pointRadius: 0,
              tension: 0.25,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          interaction: { mode: 'index', intersect: false },
          scales: {
            x: { ticks: { maxTicksLimit: 6 } },
            y: { beginAtZero: false },
          },
        },
      });
    } else {
      chart.data.labels = labels;
      chart.data.datasets[0].label = label;
      chart.data.datasets[0].data = values;
      chart.update();
    }
  }

  instrumentSelect?.addEventListener('change', () => loadChart());

  periodButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      currentPeriod = btn.dataset.period;
      setActivePeriodBtn();
      loadChart();
    });
  });

  applyCustom?.addEventListener('click', () => loadChart());

  setActivePeriodBtn();
  loadLatestTable();
  loadChart();
});