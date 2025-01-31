@extends('layouts.app')

@section('title', 'Edit Lokasi Begal')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    #map {
        height: 400px;
        width: 100%;
        margin-bottom: 20px;
    }

    /* Style untuk select risk level */
    #risk_level {
        padding: 8px;
        border-radius: 4px;
        font-weight: bold;
    }

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

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Lokasi Begal</h3>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 400px;"></div>
                    
                    <div class="row mt-2 mb-4">
                        <div class="col">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" readonly value="{{ $location->latitude }}">
                        </div>
                        <div class="col">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" readonly value="{{ $location->longitude }}">
                        </div>
                    </div>

                    <form action="{{ route('map.update', $location->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <input type="hidden" name="latitude" id="form_latitude" value="{{ $location->latitude }}">
                        <input type="hidden" name="longitude" id="form_longitude" value="{{ $location->longitude }}">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lokasi</label>
                            <input type="text" class="form-control" id="name" name="name" required value="{{ $location->name }}">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description">{{ $location->description }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="address" name="address" required value="{{ $location->address }}">
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="district" class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" id="district" name="district" value="{{ $location->district }}">
                            </div>
                            <div class="col">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $location->city }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="risk_level" class="form-label">Tingkat Risiko</label>
                            <select class="form-select" id="risk_level" name="risk_level" required>
                                <option value="low" style="background-color: #c8e6c9; color: #2e7d32;" {{ $location->risk_level == 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="medium" style="background-color: #ffe0b2; color: #ef6c00;" {{ $location->risk_level == 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="high" style="background-color: #ffcdd2; color: #c62828;" {{ $location->risk_level == 'high' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="incident_time" class="form-label">Waktu Kejadian</label>
                            <div class="input-group">
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="incident_time" 
                                       name="incident_time" 
                                       required 
                                       value="{{ date('Y-m-d\TH:i', strtotime($location->incident_time)) }}">
                                <!-- Tombol akan ditambahkan melalui JavaScript di sini -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="incident_count" class="form-label">Jumlah Kejadian</label>
                            <input type="number" class="form-control" id="incident_count" name="incident_count" value="{{ $location->incident_count }}" min="0" required>
                        </div>

                        <input type="hidden" name="is_active" value="1">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
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
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        var map = L.map('map').setView([{{ $location->latitude }}, {{ $location->longitude }}], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([{{ $location->latitude }}, {{ $location->longitude }}], {
            draggable: true
        }).addTo(map);

        map.on('click', function(e) {
            var clickedPosition = e.latlng;
            
            marker.setLatLng(clickedPosition);
            
            updateCoordinates(clickedPosition.lat, clickedPosition.lng);
            
            reverseGeocode(clickedPosition.lat, clickedPosition.lng);
        });

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
            
            marker.setLatLng(latlng);
            map.setView(latlng, 16);
            updateCoordinates(latlng.lat, latlng.lng);
            
            if (result.properties && result.properties.address) {
                var address = result.properties.address;
                
                document.getElementById('address').value = result.properties.display_name || '';
                
                var district = '';
                if (address.suburb) district = address.suburb;
                else if (address.district) district = address.district;
                else if (address.neighbourhood) district = address.neighbourhood;
                else if (address.subdistrict) district = address.subdistrict;
                else if (address.city_district) district = address.city_district;
                
                document.getElementById('district').value = district;
                
                var city = '';
                if (address.city) city = address.city;
                else if (address.town) city = address.town;
                else if (address.municipality) city = address.municipality;
                else if (address.county) city = address.county;
                
                document.getElementById('city').value = city;
            }
        }).addTo(map);

        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
            reverseGeocode(position.lat, position.lng);
        });

        function updateCoordinates(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            document.getElementById('form_latitude').value = lat.toFixed(8);
            document.getElementById('form_longitude').value = lng.toFixed(8);
        }

        function reverseGeocode(lat, lng) {
            var geocoder = L.Control.Geocoder.nominatim();
            geocoder.reverse({lat: lat, lng: lng}, map.getZoom(), function(results) {
                if (results && results.length > 0) {
                    var r = results[0];
                    if (r.properties && r.properties.address) {
                        document.getElementById('address').value = r.properties.display_name || '';
                        document.getElementById('district').value = r.properties.address.suburb || 
                                                                  r.properties.address.district || 
                                                                  r.properties.address.neighbourhood || '';
                        document.getElementById('city').value = r.properties.address.city || 
                                                              r.properties.address.town || 
                                                              r.properties.address.municipality || '';
                    }
                }
            });
        }

        setTimeout(function() {
            map.invalidateSize();
        }, 100);

        // Fungsi untuk mengupdate warna select
        function updateSelectColor(value) {
            const select = document.getElementById('risk_level');
            select.classList.remove('low-risk', 'medium-risk', 'high-risk');
            select.classList.add(`${value}-risk`);
        }

        // Set warna awal berdasarkan nilai yang ada
        updateSelectColor('{{ $location->risk_level }}');

        // Update warna saat nilai berubah
        document.getElementById('risk_level').addEventListener('change', function(e) {
            updateSelectColor(e.target.value);
            marker.setIcon(riskIcons[e.target.value]);
        });
    });
</script>
@endpush

