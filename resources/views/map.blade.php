@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lokasi</th>
                                    <th>Deskripsi</th>
                                    <th>Alamat</th>
                                    <th>Kecamatan</th>
                                    <th>Kota</th>
                                    <th>Tingkat Risiko</th>
                                    <th>Waktu Kejadian</th>
                                    <th>Jumlah Kejadian</th>
                                    <th>Status</th>
                                    <th>Koordinat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $index => $location)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->description ?? '-' }}</td>
                                    <td>{{ $location->address }}</td>
                                    <td>{{ $location->district ?? '-' }}</td>
                                    <td>{{ $location->city ?? '-' }}</td>
                                    <td>
                                        <span class="badge" style="
                                            background-color: {{ $location->risk_level == 'high' ? '#ffcdd2' : ($location->risk_level == 'medium' ? '#ffe0b2' : '#c8e6c9') }}; 
                                            color: #000000;
                                            padding: 8px 12px;
                                            border-radius: 4px;
                                            font-weight: 500;
                                        ">
                                            {{ $location->risk_level == 'high' ? 'Tinggi' : ($location->risk_level == 'medium' ? 'Sedang' : 'Rendah') }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($location->incident_time)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $location->incident_count }}</td>
                                    <td>
                                        <span class="badge bg-{{ $location->is_active ? 'success' : 'danger' }}">
                                            {{ $location->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            Lat: {{ number_format($location->latitude, 6) }}<br>
                                            Lng: {{ number_format($location->longitude, 6) }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info" onclick="focusLocation({{ $location->latitude }}, {{ $location->longitude }})">
                                                <i class="fas fa-map-marker-alt"></i> Posisi
                                            </button>
                                            <a href="/map/{{ $location->id }}/edit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="/map/{{ $location->id }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Tambahkan Leaflet Control Geocoder -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
    var map = L.map('map').setView([-7.983908, 112.621391], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Tambahkan control pencarian
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
        var bbox = e.geocode.bbox;
        map.fitBounds(bbox);
    }).addTo(map);

    function addMarker(lat, lng, location) {
        var marker = L.marker([lat, lng]);
        
        var riskStyle = {
            'high': { bg: '#ffcdd2', label: 'Tinggi' },    
            'medium': { bg: '#ffe0b2', label: 'Sedang' },  
            'low': { bg: '#c8e6c9', label: 'Rendah' }      
        };
        
        var style = riskStyle[location.risk_level];
        
        // Format tanggal dan waktu
        var incidentDate = new Date(location.incident_time).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        var popupContent = `
            <div style="min-width: 300px;">
                <h6 class="mb-3">${location.name}</h6>
                <table class="table table-sm table-bordered">
                    <tr>
                        <th style="width: 40%">Alamat</th>
                        <td>${location.address || '-'}</td>
                    </tr>
                    <tr>
                        <th>Kecamatan</th>
                        <td>${location.district || '-'}</td>
                    </tr>
                    <tr>
                        <th>Kota</th>
                        <td>${location.city || '-'}</td>
                    </tr>
                    <tr>
                        <th>Tingkat Risiko</th>
                        <td>
                            <span style="
                                background-color: ${style.bg}; 
                                color: #000000; 
                                padding: 2px 6px; 
                                border-radius: 3px;
                                font-size: 0.9em;
                            ">
                                ${style.label}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Waktu Kejadian</th>
                        <td>${incidentDate}</td>
                    </tr>
                    <tr>
                        <th>Jumlah Kejadian</th>
                        <td>${location.incident_count} kali</td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>${location.description || '-'}</td>
                    </tr>
                </table>
                <div class="btn-group w-100 mt-2">
                    <button class="btn btn-sm btn-info" onclick="focusLocation(${lat}, ${lng})">
                        <i class="fas fa-map-marker-alt"></i> Posisi
                    </button>
                    <a href="/map/${location.id}/edit" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button class="btn btn-sm btn-danger" onclick="deleteLocation(${location.id})">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent, {
            maxWidth: 400,
            className: 'custom-popup'
        });
        return marker;
    }

    @foreach($locations as $location)
        addMarker(
            {{ $location->latitude }},
            {{ $location->longitude }},
            {!! json_encode($location) !!}
        ).addTo(map);
    @endforeach

    function focusLocation(lat, lng) {
        map.setView([lat, lng], 16);
    }

    function deleteLocation(id) {
        if (confirm('Apakah Anda yakin ingin menghapus lokasi ini?')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/map/' + id;
            
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            var tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<style>
    .custom-popup .leaflet-popup-content {
        margin: 12px;
    }
    .custom-popup .table {
        margin-bottom: 0;
    }
    .custom-popup .table th,
    .custom-popup .table td {
        padding: 4px 8px;
        font-size: 0.9em;
    }
    /* Styling untuk control pencarian */
    .leaflet-control-geocoder {
        border: 2px solid rgba(0,0,0,0.2);
        border-radius: 4px;
    }
    .leaflet-control-geocoder-form input {
        padding: 5px;
        width: 200px;
    }
</style>
@endpush
@endsection