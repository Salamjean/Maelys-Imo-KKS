@extends('admin.layouts.template')

@section('content')
<div class="content-wrapper d-flex flex-column align-items-center justify-content-center">
    <div class="col-md-8">
        <div class="text-center mb-5">
            <i class="mdi mdi-chart-areaspline text-primary mb-3" style="font-size: 80px;"></i>
            <h2 class="font-weight-bold" style="color: #02245b;">Statistiques par Commercial</h2>
            <p class="text-muted">Recherchez un commercial par son nom, prénom ou son code ID (ex: COM123456) pour consulter ses performances.</p>
        </div>

        <div class="card border-0 shadow-lg mb-4" style="border-radius: 25px;">
            <div class="card-body p-4">
                <form action="{{ route('admin.commercial.statistics') }}" method="GET">
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-radius: 15px 0 0 15px;">
                                <i class="mdi mdi-magnify text-muted"></i>
                            </span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0 py-4" placeholder="Entrez le nom ou le Code ID..." value="{{ request('search') }}" style="border-radius: 0 15px 15px 0; font-size: 1.1rem;">
                        <div class="input-group-append ml-3">
                            <button class="btn btn-primary px-5" type="submit" style="background: #02245b; border: none; border-radius: 15px; font-weight: bold;">
                                Rechercher
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($isSearch)
            <div class="mt-5">
                <h5 class="font-weight-bold mb-4" style="color: #02245b;">Résultats de la recherche ({{ $commercials->count() }})</h5>
                @forelse($commercials as $commercial)
                    <div class="card border-0 shadow-sm mb-3 hover-shadow transition" style="border-radius: 15px; cursor: pointer;" onclick="window.location='{{ route('admin.commercial.statistics.show', $commercial->id) }}'">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    @if($commercial->profile_image)
                                        <img src="{{ asset('storage/' . $commercial->profile_image) }}" alt="image" class="mr-3 rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 50px; height: 50px; font-weight: bold; font-size: 1.2rem;">
                                            {{ strtoupper(substr($commercial->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="font-weight-bold mb-1" style="color: #02245b;">{{ $commercial->name }} {{ $commercial->prenom }}</h6>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light text-muted mr-2">{{ $commercial->code_id }}</span>
                                            <small class="text-muted"><i class="mdi mdi-phone mr-1"></i>{{ $commercial->contact }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button class="btn btn-inverse-primary btn-rounded btn-icon">
                                        <i class="mdi mdi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <img src="{{ asset('assets/images/no-results.png') }}" alt="No results" style="width: 150px; opacity: 0.5;" onerror="this.src='https://illustrations.popsy.co/gray/search.svg'">
                        <p class="mt-4 text-muted">Aucun commercial trouvé pour "{{ request('search') }}".</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>

<style>
    .transition { transition: all 0.3s ease; }
    .hover-shadow:hover { 
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .input-group-text, .form-control {
        border: 1px solid #e2e8f0;
    }
    .form-control:focus {
        border-color: #02245b;
        box-shadow: none;
    }
</style>
@endsection
