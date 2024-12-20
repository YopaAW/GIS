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

                    <form action="/map" method="POST">
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
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="incident_time" class="form-label">Waktu Kejadian</label>
                            <input type="datetime-local" class="form-control" id="incident_time" name="incident_time" required>
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
                            <a href="/map" class="btn btn-secondary">
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
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var map = L.map('map').setView([-7.983908, 112.621391], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([-7.983908, 112.621391], {
            draggable: true
        }).addTo(map);

        // Update control pencarian dengan data lengkap
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
            if (result.properties) {
                if (result.properties.address) {
                    var address = result.properties.address;
                    
                    // Update alamat lengkap
                    document.getElementById('address').value = result.properties.display_name || '';
                    
                    // Update kecamatan - coba berbagai kemungkinan field
                    var district = address.suburb || 
                                 address.district || 
                                 address.neighbourhood || 
                                 address.subdistrict ||
                                 address.city_district ||
                                 address.county ||
                                 ''; // default ke string kosong jika tidak ada
                    
                    document.getElementById('district').value = district;
                    
                    // Update kota
                    var city = address.city || 
                             address.town || 
                             address.municipality || 
                             address.county ||
                             '';
                    
                    document.getElementById('city').value = city;
                }
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

        // Fungsi untuk reverse geocoding
        function reverseGeocode(lat, lng) {
            var geocoder = L.Control.Geocoder.nominatim();
            geocoder.reverse({lat: lat, lng: lng}, map.getZoom(), function(results) {
                if (results && results.length > 0) {
                    var r = results[0];
                    if (r.properties && r.properties.address) {
                        var address = r.properties.address;
                        
                        document.getElementById('address').value = r.properties.display_name || '';
                        
                        // Update kecamatan dengan multiple fallbacks
                        var district = address.suburb || 
                                     address.district || 
                                     address.neighbourhood || 
                                     address.subdistrict ||
                                     address.city_district ||
                                     address.county ||
                                     '';
                        
                        document.getElementById('district').value = district;
                        
                        // Update kota dengan multiple fallbacks
                        var city = address.city || 
                                 address.town || 
                                 address.municipality || 
                                 address.county ||
                                 '';
                        
                        document.getElementById('city').value = city;
                    }
                }
            });
        }

        // Set koordinat awal
        updateCoordinates(-7.983908, 112.621391);
    });
</script>
@endpush
@endsection
