import Chart from 'chart.js/auto';

async function fetchJson(url) {
  const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return await res.json();
}

function fmt(v) {
  if (v === null || v === undefined) return '—';
  if (typeof v !== 'number') return String(v);
  return v.toLocaleString(undefined, { maximumFractionDigits: 4 });
}

document.addEventListener('DOMContentLoaded', async () => {
  const root = document.querySelector('[data-market-page]');
  if (!root) return;

  const locale = root.getAttribute('data-locale');
  const selected = root.getAttribute('data-selected');
  const beltList = root.getAttribute('data-belt-slugs');
  const dataUrlBase = root.getAttribute('data-data-url'); // like /en/market/data

  // 1) Fill latest table
  try {
    const latest = await fetchJson(`${dataUrlBase}?instruments=${encodeURIComponent(beltList)}`);

    document.querySelectorAll('[data-latest-row]').forEach((row) => {
      const slug = row.getAttribute('data-latest-row');
      const item = latest[slug];
      if (!item) return;

      row.querySelector('[data-latest-value]').textContent = fmt(item.value);
      row.querySelector('[data-latest-unit]').textContent = item.unit || '';
      row.querySelector('[data-latest-date]').textContent = item.date || '—';
    });
  } catch (e) {
    // keep silent; page still works
  }

  // 2) Chart
  const canvas = document.getElementById('marketChart');
  if (!canvas) return;

  let chart = new Chart(canvas, {
    type: 'line',
    data: { labels: [], datasets: [{ label: '', data: [] }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: true } },
      scales: {
        x: { ticks: { maxRotation: 0, autoSkip: true } },
        y: { beginAtZero: false }
      },
    },
  });

  async function loadChart(slug, period) {
    const payload = await fetchJson(`${dataUrlBase}?instrument=${encodeURIComponent(slug)}&period=${encodeURIComponent(period)}`);

    const labels = payload.points.map(p => p.date);
    const values = payload.points.map(p => p.value);

    chart.data.labels = labels;
    chart.data.datasets[0].label = `${payload.instrument.name} (${payload.instrument.unit})`;
    chart.data.datasets[0].data = values;
    chart.update();
  }

  const periodSelect = document.querySelector('[data-period]');
  const instrumentSelect = document.querySelector('[data-instrument]');

  const initialPeriod = periodSelect?.value || '3m';
  const initialInstrument = instrumentSelect?.value || selected;

  try {
    await loadChart(initialInstrument, initialPeriod);
  } catch (e) {
    // ignore
  }

  periodSelect?.addEventListener('change', async () => {
    try { await loadChart(instrumentSelect.value, periodSelect.value); } catch (e) {}
  });

  instrumentSelect?.addEventListener('change', async () => {
    try { await loadChart(instrumentSelect.value, periodSelect.value); } catch (e) {}
  });
});