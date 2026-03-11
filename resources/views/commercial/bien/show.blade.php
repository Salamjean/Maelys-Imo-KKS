@extends('commercial.layouts.template')

@section('content')
<div class="content-wrapper p-4" style="background: #f8fafc !important;">
    <style>
        :root {
            --primary: #02245b;
            --accent: #ff5e14;
            --surface: #ffffff;
        }

        .premium-card {
            background: var(--surface);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }

        .main-img-container {
            width: 100%;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .main-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumb-img {
            width: 100%;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .thumb-img:hover {
            transform: scale(1.05);
        }

        .status-pill {
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 700;
            display: inline-block;
        }
    </style>

    <div class="mb-4 d-flex align-items-center">
        <a href="{{ route('commercial.biens.index') }}" class="btn btn-light btn-sm mr-3" style="border-radius: 10px;">
            <i class="mdi mdi-arrow-left"></i> Retour
        </a>
        <h2 class="font-weight-bold mb-0" style="color: var(--primary);">Détails du Bien : {{ $bien->code_bien }}</h2>
    </div>

    <div class="row">
        <!-- Images/Video Section -->
        <div class="col-lg-7">
            @if($bien->video_3d)
                <div class="main-img-container mb-3" style="background: #000;">
                    @php $embedUrl = $bien->getVideo3dEmbedUrl(); @endphp
                    @if(str_contains($embedUrl, '<iframe'))
                        {!! $embedUrl !!}
                    @else
                        <iframe src="{{ $embedUrl }}" style="width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                    @endif
                </div>
            @else
                <div class="main-img-container mb-3">
                    <img src="{{ asset('storage/' . $bien->image) }}" id="mainDisplay" alt="Main">
                </div>
            @endif

            <div class="row no-gutters">
                <div class="col-2 p-1">
                    <img src="{{ asset('storage/' . $bien->image) }}" class="thumb-img" onclick="document.getElementById('mainDisplay').src=this.src">
                </div>
                @for($i=1; $i<=5; $i++)
                    @php $field = 'image' . $i; @endphp
                    @if($bien->$field)
                        <div class="col-2 p-1">
                            <img src="{{ asset('storage/' . $bien->$field) }}" class="thumb-img" onclick="document.getElementById('mainDisplay').src=this.src">
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        <!-- Info Section -->
        <div class="col-lg-5">
            <div class="premium-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="status-pill {{ $bien->status == 'Disponible' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                        {{ $bien->status }}
                    </span>
                    <h3 class="font-weight-bold text-primary mb-0">{{ number_format($bien->prix, 0, ',', ' ') }} FCFA</h3>
                </div>

                <div class="row mb-4">
                    <div class="col-6 mb-3">
                        <div class="info-label">Type</div>
                        <div class="info-value">{{ $bien->type }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Superficie</div>
                        <div class="info-value">{{ $bien->superficie }} m²</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Localisation</div>
                        <div class="info-value"><i class="mdi mdi-map-marker text-danger"></i> {{ $bien->commune }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Utilisation</div>
                        <div class="info-value">{{ $bien->utilisation }}</div>
                    </div>
                </div>

                <div class="row mb-4 border-top pt-3">
                    <div class="col-4 text-center">
                        <div class="info-label">Chambres</div>
                        <div class="info-value">{{ $bien->nombre_de_chambres ?? '0' }}</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="info-label">Toilettes</div>
                        <div class="info-value">{{ $bien->nombre_de_toilettes ?? '0' }}</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="info-label">Garage</div>
                        <div class="info-value">{{ $bien->garage ?? 'Non' }}</div>
                    </div>
                </div>

                <div class="border-top pt-3 mb-4">
                    <div class="info-label">Description</div>
                    <p class="text-muted small">{{ $bien->description }}</p>
                </div>

                <div class="bg-light p-3 rounded mb-4" style="border-radius: 15px !important;">
                    <div class="info-label">Propriétaire / Agence</div>
                    @if($bien->agence)
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-domain mr-2 text-primary"></i>
                            <div class="font-weight-bold">{{ $bien->agence->name }}</div>
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-account-tie mr-2 text-success"></i>
                            <div class="font-weight-bold">{{ $bien->proprietaire->name }} {{ $bien->proprietaire->prenom }}</div>
                        </div>
                    @endif
                </div>

                <div class="d-flex border-top pt-4 mt-auto">
                    <a href="{{ route('commercial.biens.edit', $bien->id) }}" class="btn btn-warning flex-grow-1 mr-2 font-weight-bold" style="border-radius: 12px;">
                        <i class="mdi mdi-pencil mr-1"></i> Modifier
                    </a>
                    <form action="{{ route('commercial.biens.destroy', $bien->id) }}" method="POST" class="flex-grow-1 ml-2 delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger w-100 font-weight-bold btn-delete-confirm" style="border-radius: 12px;">
                            <i class="mdi mdi-delete mr-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.btn-delete-confirm').on('click', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#02245b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
