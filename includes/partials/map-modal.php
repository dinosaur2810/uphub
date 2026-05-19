<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapModalLabel">Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="mapContainer" style="height: 400px; width: 100%;"></div>
        <div class="p-3">
          <div id="locationInfo" class="small text-muted"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let map = null;
    let currentMarker = null;
    
    // Initialize map when modal is shown
    document.getElementById('mapModal').addEventListener('shown.bs.modal', function() {
        if (!map) {
            map = L.map('mapContainer').setView([14.5995, 120.9842], 13); // Default to Manila
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
        }
        
        // Resize map to fix rendering issues
        setTimeout(function() {
            map.invalidateSize();
        }, 100);
    });
    
    // Handle View Location button clicks
    document.addEventListener('click', function(e) {
        if (e.target.hasAttribute('data-view-location')) {
            const address = e.target.getAttribute('data-address');
            const title = e.target.getAttribute('data-title');
            
            // Update modal title
            document.getElementById('mapModalLabel').textContent = title;
            
            // Show loading state
            document.getElementById('locationInfo').innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Locating address...';
            
            // Geocode the address
            geocodeAddress(address, title);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('mapModal'));
            modal.show();
        }
    });
    
    function geocodeAddress(address, title) {
        // Use Nominatim API for geocoding
        const apiUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);
                    
                    // Center map on location
                    map.setView([lat, lon], 16);
                    
                    // Remove existing marker
                    if (currentMarker) {
                        map.removeLayer(currentMarker);
                    }
                    
                    // Add new marker
                    currentMarker = L.marker([lat, lon]).addTo(map);
                    currentMarker.bindPopup(`<strong>${title}</strong><br>${address}`).openPopup();
                    
                    // Update location info
                    document.getElementById('locationInfo').innerHTML = 
                        `<strong>Address:</strong> ${address}<br>
                         <strong>Coordinates:</strong> ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                } else {
                    // Address not found
                    document.getElementById('locationInfo').innerHTML = 
                        `<div class="text-warning">
                            <strong>Location not found</strong><br>
                            Could not locate: ${address}
                        </div>`;
                    
                    // Reset view to default location
                    map.setView([14.5995, 120.9842], 13);
                    
                    if (currentMarker) {
                        map.removeLayer(currentMarker);
                        currentMarker = null;
                    }
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                document.getElementById('locationInfo').innerHTML = 
                    `<div class="text-danger">
                        <strong>Error locating address</strong><br>
                        Please try again later.
                    </div>`;
            });
    }
});
</script>
