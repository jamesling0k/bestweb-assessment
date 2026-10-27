document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('swagger-ui');
    if (typeof SwaggerUIBundle === 'undefined') {
        container.textContent = 'The interactive viewer could not load. Use the OpenAPI specification link above.';
        return;
    }
    SwaggerUIBundle({
        url: container.dataset.specUrl,
        dom_id: '#swagger-ui',
        deepLinking: true,
        persistAuthorization: false,
    });
});
