/**
 * Hay API Gateway Service Worker
 * Provides offline caching and performance optimization
 */

const CACHE_NAME = 'hay-v1';
const STATIC_CACHE_NAME = 'hay-static-v1';
const DYNAMIC_CACHE_NAME = 'hay-dynamic-v1';

// Static assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/css/variables.css',
    '/css/themes/light.css',
    '/css/themes/dark.css',
    '/css/components.css',
    '/css/global.css',
    '/css/layout.css',
    '/js/theme-switcher.js',
    '/js/bottom-sheet.js',
    '/js/notifications.js',
    '/js/charts.js',
    '/js/global.js',
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// API paths that should always be network-first
const API_PATHS = [
    '/api/',
    '/v1/',
    '/login',
    '/logout',
    '/register'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS.map(url => {
                    return new Request(url, { cache: 'reload' });
                })).catch(err => {
                    console.log('[SW] Some assets failed to cache:', err);
                });
            })
            .then(() => {
                console.log('[SW] Static assets cached');
                return self.skipWaiting();
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            return cacheName.startsWith('hay-') && 
                                   cacheName !== STATIC_CACHE_NAME && 
                                   cacheName !== DYNAMIC_CACHE_NAME;
                        })
                        .map((cacheName) => {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[SW] Claiming clients');
                return self.clients.claim();
            })
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    
    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }
    
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }
    
    // Check if this is an API request (network-first)
    const isApiRequest = API_PATHS.some(path => url.pathname.startsWith(path));
    
    if (isApiRequest) {
        // Network-first for API requests
        event.respondWith(networkFirst(event.request));
    } else if (isStaticAsset(url.pathname)) {
        // Cache-first for static assets
        event.respondWith(cacheFirst(event.request));
    } else {
        // Network-first with cache fallback for pages
        event.respondWith(networkFirst(event.request));
    }
});

// Check if request is for a static asset
function isStaticAsset(pathname) {
    const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf'];
    return staticExtensions.some(ext => pathname.endsWith(ext)) || 
           pathname.startsWith('/icons/') ||
           pathname === '/manifest.json';
}

// Cache-first strategy
async function cacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            // Return cached response and update cache in background
            updateCache(request);
            return cachedResponse;
        }
        
        // Not in cache, fetch from network
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Cache-first failed:', error);
        return caches.match(request);
    }
}

// Network-first strategy
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        // Cache successful GET responses for pages
        if (networkResponse.ok && request.method === 'GET') {
            const cache = await caches.open(DYNAMIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[SW] Network-first failed, trying cache:', error);
        
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline page if available
        if (request.headers.get('Accept')?.includes('text/html')) {
            return caches.match('/');
        }
        
        throw error;
    }
}

// Update cache in background
async function updateCache(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE_NAME);
            cache.put(request, networkResponse);
        }
    } catch (error) {
        // Silently fail - we already have cached version
    }
}

// Handle messages from the main app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        caches.keys().then((cacheNames) => {
            cacheNames.forEach((cacheName) => {
                if (cacheName.startsWith('hay-')) {
                    caches.delete(cacheName);
                }
            });
        });
    }
});
