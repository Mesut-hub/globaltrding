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
  const defaultPeriod = new URLSearchParams(window.location.search).get('period') || '3m';

  const instrumentSelect = document.getElementById('instrumentSelect');
  const periodButtons = document.querySelectorAll('.periodBtn');
  const customRange = document.getElementById('customRange');
  const fromDate = document.getElementById('fromDate');
  const toDate = document.getElementById('toDate');
  const applyCustom = document.getElementById('applyCustom');

  const rangeText = document.getElementById('rangeText');
  const titleText = document.getElementById('titleText');
  const latestText = document.getElementById('latestText');
  const statHigh = document.getElementById('statHigh');
  const statLow = document.getElementById('statLow');
  const statChange = document.getElementById('statChange');
  const marketStatus = document.getElementById('marketStatus');
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

    const urlState = new URLSearchParams();
    urlState.set('instrument', instrument);
    urlState.set('period', currentPeriod);
    if (currentPeriod === 'custom') {
      if (fromDate?.value) urlState.set('from', fromDate.value);
      if (toDate?.value) urlState.set('to', toDate.value);
    }
    window.history.replaceState({}, '', `/${locale}/market?${urlState.toString()}`);

    marketStatus.textContent = 'Updating chart…';
    const url = `/${locale}/market/data?${params.toString()}`;
    let json;
    try {
      json = await fetchJson(url);
    } catch (e) {
      marketStatus.textContent = 'Unable to load chart data';
      emptyHint.classList.remove('hidden');
      return;
    }

    const points = json.points || [];
    const labels = points.map((p) => p.date);
    const values = points.map((p) => p.value);

    rangeText.textContent = `${json.range.from} → ${json.range.to}`;

    const label = pickLocaleName(json.instrument?.name, locale) || json.instrument?.slug || 'Market';
    titleText.textContent = label;

    const lastVal = values.length ? values[values.length - 1] : null;
    latestText.textContent = fmt(lastVal);

    const numericValues = values.filter((v) => typeof v === 'number' && !Number.isNaN(v));
    const high = numericValues.length ? Math.max(...numericValues) : null;
    const low = numericValues.length ? Math.min(...numericValues) : null;
    const first = numericValues.length ? numericValues[0] : null;
    const change = first !== null && lastVal !== null ? lastVal - first : null;

    statHigh.textContent = fmt(high);
    statLow.textContent = fmt(low);
    if (change === null) {
      statChange.textContent = '—';
      statChange.classList.remove('text-emerald-600', 'text-rose-600');
      statChange.classList.add('text-slate-900');
    } else {
      const sign = change > 0 ? '+' : '';
      statChange.textContent = `${sign}${fmt(change)}`;
      statChange.classList.toggle('text-emerald-600', change > 0);
      statChange.classList.toggle('text-rose-600', change < 0);
      statChange.classList.toggle('text-slate-900', change === 0);
    }

    emptyHint.classList.toggle('hidden', values.length > 0);
    marketStatus.textContent = values.length ? 'Live feed ready' : 'No data for selected range';

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
              borderColor: '#0f766e',
              backgroundColor: 'rgba(15,118,110,0.08)',
              fill: true,
              tension: 0.25,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          interaction: { mode: 'index', intersect: false, axis: 'x' },
          scales: {
            x: { ticks: { maxTicksLimit: 6 }, grid: { color: 'rgba(15,23,42,0.06)' } },
            y: { beginAtZero: false, grid: { color: 'rgba(15,23,42,0.06)' } },
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
  if (!['1m', '3m', '1y', 'custom'].includes(currentPeriod)) currentPeriod = '3m';
  loadChart();
});
