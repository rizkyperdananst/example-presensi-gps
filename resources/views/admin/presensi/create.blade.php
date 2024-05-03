@extends('template')
@section('title', 'Admin | Presensi Create')

@section('content')
    @push('styles')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <style>
            .webcam-capture,
            .webcam-capture video {
                border: 2px solid black;
            }

            #map {
                height: 300px;
            }
        </style>
    @endpush

    <div class="row mt-3 mb-3">
        <div class="col-md-12">
            <button class="btn btn-dark btn-sm mb-5">Webcamp</button>
            <input type="hidden" name="" id="lokasi">
            <div class="webcam-capture"></div>
        </div>
    </div>

    @if ($check > 0)
    <div class="row mt-3 mb-3">
        <div class="col-md-2">
            <button id="takeabsen" class="btn btn-danger btn-block">
                Absen Pulang
            </button>
        </div>
    </div>
    @else
    <div class="row mt-3 mb-3">
        <div class="col-md-2">
            <button id="takeabsen" class="btn btn-primary btn-block">
                Absen Masuk
            </button>
        </div>
    </div>
    @endif

    <div class="row mt-2 mb-2">
        <div class="col-md-12">
            <div id="map"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {

                let lokasi = document.getElementById('lokasi');

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
                }

                function successCallback(position) {
                    lokasi.value = position.coords.latitude + ', ' + position.coords.longitude;
                    let latitude = position.coords.latitude;
                    let longitude = position.coords.longitude;
                    var map = L.map('map').setView([latitude, longitude], 15);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);
                    var marker = L.marker([latitude, longitude]).addTo(map);
                    // var cirle ini fungsinya untuk membuat lingkaran radius titik koordinat untuk absensi
                    var circle = L.circle([2.965847, 99.0625], {
                        color: 'red',
                        fillColor: '#f03',
                        fillOpacity: 0.5,
                        radius: 10 // fungsinya untuk mengatur radius agar si user hanya bisa absen di radius 20 meter dari kantor
                    }).addTo(map);

                    var popup = L.popup();

                    function onMapClick(e) {
                        popup
                            .setLatLng(e.latlng)
                            .setContent("You clicked the map at " + e.latlng.toString())
                            .openOn(map);
                    }

                    map.on('click', onMapClick);
                }

                function errorCallback() {
                    lokasi.value = 'Failed to get location';
                }

                Webcam.set({
                    width: 320,
                    height: 240,
                    image_format: 'jpeg',
                    jpeg_quality: 90
                });

                Webcam.attach('.webcam-capture');

                // ajax
                $("#takeabsen").click(function(e) {
                    Webcam.snap(function(uri) {
                        image = uri;
                    });

                    var lokasi = $("#lokasi").val();

                    $.ajax({
                        type: "POST",
                        url: "{{ route('admin.absensi.store') }}",
                        data: {
                            _token: "{{ csrf_token() }}",
                            lokasi: lokasi,
                            image: image
                        },
                        cache: false,
                        success: function(response) {
                            let status = response.split('|');
                            if (status[0] == 'success') {
                                Swal.fire({
                                    title: 'Success!',
                                    text: status[1],
                                    icon: 'success',
                                })
                                setTimeout(() => {
                                    location.reload();
                                }, 3000);
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: status[1],
                                    icon: 'error',
                                })
                            }
                        }
                    });

                });
                // end ajax
            });
        </script>
    @endpush
@endsection
