/* Service worker Musubi Dojo — hors-ligne prudent.
   HTML : réseau d'abord (contenu toujours frais), repli cache puis page hors-ligne.
   Statiques : cache d'abord. /admin/, formulaires et POST : jamais interceptés. */
var VERSION = 'mdj-v1';
var CORE = [
  '/offline.html',
  '/images/logo-or.webp',
  '/images/icon-192.png',
  '/images/favicon-32.png'
];

self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(VERSION).then(function (c) { return c.addAll(CORE); }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (e) {
  e.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.filter(function (k) { return k !== VERSION; }).map(function (k) { return caches.delete(k); }));
    }).then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function (e) {
  var req = e.request;
  if (req.method !== 'GET') return;

  var url;
  try { url = new URL(req.url); } catch (err) { return; }

  // Même origine uniquement (on laisse passer Google Fonts, Facebook, etc.)
  if (url.origin !== self.location.origin) return;

  // Ne jamais intercepter l'admin ni les traitements de formulaire
  if (url.pathname.indexOf('/admin') === 0 || url.pathname.indexOf('/contact.php') === 0) return;

  var isHTML = req.mode === 'navigate' ||
    (req.headers.get('accept') || '').indexOf('text/html') !== -1;

  if (isHTML) {
    // Réseau d'abord
    e.respondWith(
      fetch(req).then(function (res) {
        var copy = res.clone();
        caches.open(VERSION).then(function (c) { c.put(req, copy); });
        return res;
      }).catch(function () {
        return caches.match(req).then(function (hit) { return hit || caches.match('/offline.html'); });
      })
    );
    return;
  }

  // Statiques : cache d'abord
  e.respondWith(
    caches.match(req).then(function (hit) {
      return hit || fetch(req).then(function (res) {
        if (res && res.status === 200 && res.type === 'basic') {
          var copy = res.clone();
          caches.open(VERSION).then(function (c) { c.put(req, copy); });
        }
        return res;
      }).catch(function () { return hit; });
    })
  );
});
