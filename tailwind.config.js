/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./vendor/filament/**/*.blade.php",
  ],
  safelist: [
    // All md:col-span-N values you ever use in dynamic contexts
    { pattern: /^md:col-span-(1[0-6]|[1-9])$/ },
    // Hero CTA button classes injected by JS slider
    'bg-white',
    'text-slate-900',
    'hover:bg-slate-100',
    'text-white',
    'border-white/30',
    'hover:bg-white/10',
    'rounded-md',
    'font-medium',
    'px-5',
    'py-2.5',
  ],
  theme: {
    extend: {
      gridTemplateColumns: {
        16: 'repeat(16, minmax(0, 1fr))',
      },
    },
  },
  plugins: [],
};