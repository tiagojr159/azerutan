const CACHE_NAME = 'azerutan-pwa-v1';
const APP_SHELL = [
  './manifest.json',
  './pwa-icon-192.png',
  './pwa-icon-512.png',
  './pwa-icon.svg',
  './pwa-install.js',
  './template/bootstrap.min.css',
  './template/jquery-3.3.1.slim.min.js',
  './template/popper.min.js',
  './template/bootstrap.min.js'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function (cache) {
        return Promise.all(APP_SHELL.map(function (url) {
          return cache.add(url).catch(function () {
            return null;
          });
        }));
      })
      .then(function () {
        return self.skipWaiting();
      })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys()
      .then(function (keys) {
        return Promise.all(keys.map(function (key) {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        }));
      })
      .then(function () {
        return self.clients.claim();
      })
  );
});

self.addEventListener('fetch', function (event) {
  var request = event.request;
  var requestUrl = new URL(request.url);

  if (request.method !== 'GET' || requestUrl.origin !== self.location.origin) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(function () {
        return new Response(
          '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Azerutan</title><body><h1>Azerutan</h1><p>Sem conexao no momento. Tente novamente quando a internet voltar.</p></body></html>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      })
    );
    return;
  }

  if (/\.(?:css|js|png|jpe?g|gif|svg|webp|ico|woff2?)$/i.test(requestUrl.pathname)) {
    event.respondWith(
      caches.match(request).then(function (cachedResponse) {
        if (cachedResponse) {
          return cachedResponse;
        }

        return fetch(request).then(function (networkResponse) {
          var responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then(function (cache) {
            cache.put(request, responseClone);
          });
          return networkResponse;
        });
      })
    );
  }
});
