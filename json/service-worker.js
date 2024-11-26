self.addEventListener('install', (event) => {
    console.log('Service Worker installed');
  });
  
  self.addEventListener('fetch', (event) => {
    console.log('Fetching:', event.request.url);
    // Let the request go through as normal, no caching
    event.respondWith(fetch(event.request));
  });
  