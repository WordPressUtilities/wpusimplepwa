if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(serviceWorkerUrl).then(function(registration) {
        console.log('Registration successful, scope is:', registration.scope);
        navigator.serviceWorker.register(serviceWorkerUrl, {
            scope: '/'
        });
    }).catch(function(error) {
        console.log('Service worker registration failed, error:', error);
    });
}
