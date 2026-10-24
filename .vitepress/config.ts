import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vitepress'

// The markdown in this repository is the source: the site is a view of it, not a copy.
// Everything links the same way on GitHub and on the site, so srcDir is the repository root.
export default defineConfig({
  title: 'laravel-mosparo',
  description: 'mosparo spam protection for Laravel and Livewire.',
  base: '/laravel-mosparo/',
  lastUpdated: true,
  cleanUrls: true,

  srcExclude: [
    'vendor/**',
    'node_modules/**',
    'tests/**',
    'workbench/**',
    'docker/**',
    'config/**',
    'art/**',
  ],

  // README.md is the front page here, and stays the front page on GitHub.
  rewrites: {
    'README.md': 'index.md',
    'docs/README.md': 'docs/index.md',
  },

  head: [
    ['link', { rel: 'icon', type: 'image/png', href: '/laravel-mosparo/mo.png' }],
    ['meta', { property: 'og:image', content: 'https://exeque.github.io/laravel-mosparo/cover.jpg' }],
  ],

  // Mo and the cover are served from the art folder.
  vite: {
    publicDir: fileURLToPath(new URL('../art', import.meta.url)),
    server: {
      watch: {
        ignored: ['**/vendor/**', '**/workbench/**', '**/tests/**', '**/.phpunit.cache/**'],
      },
    },
  },

  themeConfig: {
    logo: '/mo.png',

    nav: [
      { text: 'Documentation', link: '/docs/' },
      { text: 'Packagist', link: 'https://packagist.org/packages/exeque/laravel-mosparo' },
    ],

    sidebar: [
      {
        text: 'Getting started',
        items: [
          { text: 'Introduction', link: '/' },
          { text: 'Installation', link: '/docs/installation' },
          { text: 'Documentation index', link: '/docs/' },
        ],
      },
      {
        text: 'Protecting a form',
        items: [
          { text: 'The box', link: '/docs/box' },
          { text: 'Plain forms', link: '/docs/plain-forms' },
          { text: 'Livewire', link: '/docs/livewire' },
          { text: 'The facade', link: '/docs/facade' },
          { text: 'Reading data', link: '/docs/data' },
          { text: 'Caching', link: '/docs/caching' },
        ],
      },
      {
        text: 'Advanced',
        items: [
          { text: 'Several projects', link: '/docs/projects' },
          { text: 'Disabling mosparo', link: '/docs/switching-off' },
          { text: 'Translations', link: '/docs/translations' },
          { text: 'Testing your application', link: '/docs/testing' },
          { text: 'Configuration', link: '/docs/configuration' },
          { text: 'How it works', link: '/docs/how-it-works' },
        ],
      },
      {
        text: 'Contributing',
        items: [
          { text: 'Contributing', link: '/CONTRIBUTING' },
        ],
      },
    ],

    search: {
      provider: 'local',
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/ExeQue/laravel-mosparo' },
    ],

    editLink: {
      pattern: 'https://github.com/ExeQue/laravel-mosparo/edit/main/:path',
      text: 'Edit this page on GitHub',
    },

    outline: [2, 3],
  },
})
