const swUrlPath = localized_vars["swUrlPath"];

window.addEventListener('load', () => {
    registerSW();
});

// Register the Service Worker
async function registerSW() {
    if ('serviceWorker' in navigator) {
        try {
            await navigator
                .serviceWorker
                .register(swUrlPath, { scope: '/' });
        }
        catch (e) {
            console.warn('SW registration failed with ', e);
        }
    }
}