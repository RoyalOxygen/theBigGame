/* NARFCARD installable app — static assets only, no PHP/API caching */
var CACHE = "narfcard-shell-v1";
var SHELL = [
  "./",
  "./offline.html",
  "./manifest.json",
  "./assets/css/app.css",
  "./assets/js/app.js",
  "./assets/js/install.js",
  "./assets/images/logo.svg",
  "./assets/images/favicon.svg",
  "./assets/images/icon-192.png",
  "./assets/images/icon-512.png",
  "./assets/images/apple-touch-icon.png"
];

self.addEventListener("install", function (event) {
  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      return cache.addAll(SHELL);
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener("activate", function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
    }).then(function () {
      return self.clients.claim();
    })
  );
});

self.addEventListener("fetch", function (event) {
  var req = event.request;
  if (req.method !== "GET") {
    return;
  }
  var url = new URL(req.url);
  if (url.origin !== self.location.origin) {
    return;
  }
  if (url.pathname.indexOf("/api/") === 0 || url.pathname.indexOf("/admin/") === 0) {
    return;
  }

  if (req.mode === "navigate") {
    event.respondWith(
      fetch(req).catch(function () {
        return caches.match("./offline.html");
      })
    );
    return;
  }

  event.respondWith(
    caches.match(req).then(function (cached) {
      if (cached) {
        return cached;
      }
      return fetch(req).then(function (res) {
        return res;
      }).catch(function () {
        return cached;
      });
    })
  );
});
