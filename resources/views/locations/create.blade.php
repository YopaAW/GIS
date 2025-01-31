@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>Tambah Lokasi Begal</h3>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 400px;"></div>
                    
                    <!-- Pindahkan Latitude Longitude ke bawah peta -->
                    <div class="row mt-2 mb-4">
                        <div class="col">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" readonly>
                        </div>
                        <div class="col">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" readonly>
                        </div>
                    </div>

                    <form action="{{ route('locations.store') }}" method="POST">
                        @csrf
                        <!-- Hidden inputs untuk lat/lng -->
                        <input type="hidden" name="latitude" id="form_latitude">
                        <input type="hidden" name="longitude" id="form_longitude">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lokasi</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="district" class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" id="district" name="district">
                            </div>
                            <div class="col">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="risk_level" class="form-label">Tingkat Risiko</label>
                            <select class="form-select" id="risk_level" name="risk_level" required>
                                <option value="low" style="background-color: #c8e6c9; color: #2e7d32;">Rendah</option>
                                <option value="medium" style="background-color: #ffe0b2; color: #ef6c00;">Sedang</option>
                                <option value="high" style="background-color: #ffcdd2; color: #c62828;">Tinggi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="incident_time" class="form-label">Waktu Kejadian</label>
                            <div class="input-group">
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="incident_time" 
                                       name="incident_time" 
                                       required>
                                <!-- Tombol akan ditambahkan melalui JavaScript di sini -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="incident_count" class="form-label">Jumlah Kejadian</label>
                            <input type="number" class="form-control" id="incident_count" name="incident_count" value="0" min="0" required>
                        </div>

                        <input type="hidden" name="is_active" value="1">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        margin-bottom: 20px;
    }

    /* Styling untuk select risk level */
    #risk_level option[value="high"] {
        background-color: #ffcdd2;
        color: #c62828;
        font-weight: bold;
    }

    #risk_level option[value="medium"] {
        background-color: #ffe0b2;
        color: #ef6c00;
        font-weight: bold;
    }

    #risk_level option[value="low"] {
        background-color: #c8e6c9;
        color: #2e7d32;
        font-weight: bold;
    }

    #risk_level {
        padding: 8px;
        border-radius: 4px;
        font-weight: bold;
    }

    /* Warna default untuk select */
    #risk_level.low-risk {
        background-color: #c8e6c9;
        color: #2e7d32;
    }

    #risk_level.medium-risk {
        background-color: #ffe0b2;
        color: #ef6c00;
    }

    #risk_level.high-risk {
        background-color: #ffcdd2;
        color: #c62828;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([-6.5882, 110.6676], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Definisikan icon untuk setiap tingkat risiko
        var riskIcons = {
            'high': L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='background-color: #ff4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 4px rgba(0,0,0,0.5);'></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            }),
            'medium': L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='background-color: #ffa000; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 4px rgba(0,0,0,0.5);'></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            }),
            'low': L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='background-color: #4caf50; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 4px rgba(0,0,0,0.5);'></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            })
        };

        var marker = L.marker([-6.5882, 110.6676], {
            draggable: true,
            icon: riskIcons['low'] // default icon
        }).addTo(map);

        // Fungsi untuk mengupdate warna select
        function updateSelectColor(value) {
            const select = document.getElementById('risk_level');
            select.classList.remove('low-risk', 'medium-risk', 'high-risk');
            select.classList.add(`${value}-risk`);
        }

        // Set warna awal
        updateSelectColor('low');

        // Update warna saat nilai berubah
        document.getElementById('risk_level').addEventListener('change', function(e) {
            updateSelectColor(e.target.value);
            marker.setIcon(riskIcons[e.target.value]);
        });

        // Tambahkan event click pada map
        map.on('click', function(e) {
            var clickedPosition = e.latlng;
            
            // Pindahkan marker ke posisi yang diklik
            marker.setLatLng(clickedPosition);
            
            // Update koordinat
            updateCoordinates(clickedPosition.lat, clickedPosition.lng);
            
            // Lakukan reverse geocoding untuk mendapatkan alamat
            reverseGeocode(clickedPosition.lat, clickedPosition.lng);
        });

        // Kode yang sudah ada untuk pencarian lokasi
        L.Control.geocoder({
            defaultMarkGeocode: false,
            position: 'topleft',
            placeholder: 'Cari lokasi...',
            errorMessage: 'Lokasi tidak ditemukan',
            showResultIcons: true,
            suggestMinLength: 3,
            suggestTimeout: 250,
            queryMinLength: 1
        }).on('markgeocode', function(e) {
            var result = e.geocode;
            var latlng = result.center;
            
            // Update marker dan view
            marker.setLatLng(latlng);
            map.setView(latlng, 16);
            
            // Update koordinat
            updateCoordinates(latlng.lat, latlng.lng);
            
            // Update alamat dan lokasi
            if (result.properties && result.properties.address) {
                var address = result.properties.address;
                
                // Update alamat lengkap
                document.getElementById('address').value = result.properties.display_name || '';
                
                // Update kecamatan dengan pengecekan yang lebih baik
                var district = '';
                if (address.suburb) district = address.suburb;
                else if (address.district) district = address.district;
                else if (address.neighbourhood) district = address.neighbourhood;
                else if (address.subdistrict) district = address.subdistrict;
                else if (address.city_district) district = address.city_district;
                
                document.getElementById('district').value = district;
                
                // Update kota dengan pengecekan yang lebih baik
                var city = '';
                if (address.city) city = address.city;
                else if (address.town) city = address.town;
                else if (address.municipality) city = address.municipality;
                else if (address.county) city = address.county;
                
                document.getElementById('city').value = city;
            }
        }).addTo(map);

        // Update koordinat saat marker di-drag
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
            
            // Reverse geocoding saat marker di-drag
            reverseGeocode(position.lat, position.lng);
        });

        function updateCoordinates(lat, lng) {
            // Update display coordinates
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            // Update hidden form coordinates
            document.getElementById('form_latitude').value = lat.toFixed(8);
            document.getElementById('form_longitude').value = lng.toFixed(8);
        }

        // Perbaikan fungsi reverseGeocode
        function reverseGeocode(lat, lng) {
            var geocoder = L.Control.Geocoder.nominatim();
            geocoder.reverse({lat: lat, lng: lng}, map.getZoom(), function(results) {
                if (results && results.length > 0) {
                    var r = results[0];
                    if (r.properties && r.properties.address) {
                        var address = r.properties.address;
                        
                        document.getElementById('address').value = r.properties.display_name || '';
                        
                        // Update kecamatan dengan pengecekan yang lebih baik
                        var district = '';
                        if (address.suburb) district = address.suburb;
                        else if (address.district) district = address.district;
                        else if (address.neighbourhood) district = address.neighbourhood;
                        else if (address.subdistrict) district = address.subdistrict;
                        else if (address.city_district) district = address.city_district;
                        
                        document.getElementById('district').value = district;
                        
                        // Update kota dengan pengecekan yang lebih baik
                        var city = '';
                        if (address.city) city = address.city;
                        else if (address.town) city = address.town;
                        else if (address.municipality) city = address.municipality;
                        else if (address.county) city = address.county;
                        
                        document.getElementById('city').value = city;
                    }
                }
            });
        }

        // Set koordinat awal ke form
        updateCoordinates(-6.5882, 110.6676);

        // Fungsi untuk mendapatkan waktu saat ini dalam format yang sesuai
        function getCurrentDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Tambahkan tombol Hari Ini ke input group
        const incidentTimeInput = document.getElementById('incident_time');
        const todayBtn = document.createElement('button');
        todayBtn.type = 'button';
        todayBtn.className = 'btn btn-secondary';
        todayBtn.innerHTML = '<i class="fas fa-calendar-day"></i> Hari Ini';
        todayBtn.onclick = function() {
            incidentTimeInput.value = getCurrentDateTime();
        };
        
        incidentTimeInput.parentNode.appendChild(todayBtn);
    });
</script>
@endpush
@endsection
