window.onload = function() {
    window.ui = SwaggerUIBundle({
        url: "/api/documentation",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIStandalonePreset
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout",
        onComplete: function() {
            // Auto-select the Sowgatly server
            var serverSelector = document.querySelector('.servers select');
            if (serverSelector) {
                var options = serverSelector.options;
                for (var i = 0; i < options.length; i++) {
                    if (options[i].textContent.includes('Sowgatly')) {
                        serverSelector.selectedIndex = i;
                        // Trigger change event to update the UI
                        var event = new Event('change');
                        serverSelector.dispatchEvent(event);
                        break;
                    }
                }
            }
        }
    });
}