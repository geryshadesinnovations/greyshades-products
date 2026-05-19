/* Greyshades - Service Worker for offline video caching */
const CACHE_NAME = 'gs-offline-v1';
const OFFLINE_VIDEOS_STORE = 'gs-offline-videos';

self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(clients.claim());
});

self.addEventListener('fetch', (e) => {
    const url = new URL(e.request.url);
    
    // Serve cached offline videos
    if (url.pathname.startsWith('/stream/') && e.request.method === 'GET') {
        e.respondWith(
            caches.match(e.request).then(cached => {
                if (cached) return cached;
                return fetch(e.request);
            })
        );
        return;
    }
    
    // For thumbnails - cache-first strategy
    if (url.pathname.startsWith('/thumb/')) {
        e.respondWith(
            caches.match(e.request).then(cached => {
                if (cached) return cached;
                return fetch(e.request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
                    }
                    return response;
                });
            })
        );
        return;
    }
});

// Listen for messages from the main thread to cache videos for offline
self.addEventListener('message', (e) => {
    if (e.data.type === 'CACHE_VIDEO') {
        const { url, title } = e.data;
        caches.open(CACHE_NAME).then(cache => {
            fetch(url).then(response => {
                if (response.ok) {
                    cache.put(url, response);
                    // Notify all clients
                    self.clients.matchAll().then(clients => {
                        clients.forEach(client => {
                            client.postMessage({ type: 'VIDEO_CACHED', url, title });
                        });
                    });
                }
            });
        });
    }
    
    if (e.data.type === 'REMOVE_CACHED_VIDEO') {
        caches.open(CACHE_NAME).then(cache => cache.delete(e.data.url));
    }
});
