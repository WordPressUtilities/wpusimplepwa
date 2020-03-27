/* ----------------------------------------------------------
  Install action
---------------------------------------------------------- */

self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open(cacheName).then(function(cache) {
            return cache.addAll(appShellFiles);
        })
    );
});

/* ----------------------------------------------------------
  Fetch action
---------------------------------------------------------- */

self.addEventListener('fetch', function(e) {

    /* Ignore get requests */
    if (e.request.method !== 'GET') {
        e.respondWith(fetch(e.request));
        return;
    }

    /* Ignore outbound requests */
    var reqUrl = new URL(e.request.url);
    if (reqUrl.hostname != appHost) {
        e.respondWith(fetch(e.request));
        return;
    }

    /* Ignore WP-admin requests */
    if (reqUrl.pathname.indexOf('wp-admin') !== -1 || reqUrl.pathname.indexOf('wp-login') !== -1) {
        e.respondWith(fetch(e.request));
        return;
    }

    e.respondWith(
        caches.match(e.request).then(function(r) {
            console.log('[Service Worker] Fetching resource: ' + e.request.url);
            return r || fetch(e.request).then(function(response) {
                return caches.open(cacheName).then(function(cache) {
                    console.log('[Service Worker] Caching new resource: ' + e.request.url);
                    cache.put(e.request, response.clone());
                    return response;
                });
            });
        })
    );
});
