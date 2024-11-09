const CACHE_NAME = 'db-search-cache-v1';
const urlsToCache = [
  '/index.php',
  '/styles.css',
  '/app.js',
  'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap',
  'https://code.jquery.com/jquery-3.6.0.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request))
  );
});