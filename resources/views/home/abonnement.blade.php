@extends('home.pages.layouts.template')
@section('content')
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4" style="color: #02245b;">Choisissez Votre Abonnement</h1>
        <p class="lead">Sélectionnez le forfait qui correspond à vos besoins</p>
    </div>

    <!-- Toggle Switch for User Type -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6 text-center">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active" style="border-color: #02245b; color: #02245b;">
                    <input type="radio" name="userType" id="proprietaire" autocomplete="off" checked> Propriétaire
                </label>
                <label class="btn btn-outline-primary" style="border-color: #02245b; color: #02245b;">
                    <input type="radio" name="userType" id="agence" autocomplete="off"> Agence
                </label>
            </div>
        </div>
    </div>

    <!-- Proprietaire Subscription Plans (Visible by default) -->
    <div id="proprietaire-plans" class="row justify-content-center">
        <!-- Standard Plan -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-header py-3 text-white" style="background-color: #02245b;">
                    <h4 class="my-0 font-weight-normal text-center" style="color:white">Standard</h4>
                </div>
                <div class="card-body">
                    <h2 class="card-title text-center">5 000 <small>FCFA</small></h2>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> 5 biens maximum</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Visibilité sur la plateforme</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Support de base</li>
                        <li class="mb-2"><i class="fas fa-times text-danger mr-2"></i> Statistiques avancées</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 pb-4">
                    <button type="button" class="btn btn-lg btn-block" style="background-color: #ff5e14; color: white;">Souscrire</button>
                </div>
            </div>
        </div>

        <!-- Premium Plan -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-header py-3 text-white" style="background-color: #ff5e14;">
                    <h4 class="my-0 font-weight-normal text-center">Premium</h4>
                </div>
                <div class="card-body">
                    <h2 class="card-title text-center">7 000 <small>FCFA</small></h2>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Nombre de biens illimité</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Visibilité prioritaire</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Support premium</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Statistiques avancées</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 pb-4">
                    <button type="button" class="btn btn-lg btn-block" style="background-color: #02245b; color: white;">Souscrire</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Agence Subscription Plans (Hidden by default) -->
    <div id="agence-plans" class="row justify-content-center" style="display: none;">
        <!-- Standard Plan -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-header py-3 text-white" style="background-color: #02245b;">
                    <h4 class="my-0 font-weight-normal text-center" style="color:white">Standard</h4>
                </div>
                <div class="card-body">
                    <h2 class="card-title text-center">10 000 <small>FCFA</small></h2>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> 15 biens maximum</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Visibilité sur la plateforme</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Support de base</li>
                        <li class="mb-2"><i class="fas fa-times text-danger mr-2"></i> Statistiques avancées</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 pb-4">
                    <button type="button" class="btn btn-lg btn-block" style="background-color: #ff5e14; color: white;">Souscrire</button>
                </div>
            </div>
        </div>

        <!-- Premium Plan -->
        <div class="col-md-5 mb-4">
            <div class="card h-100 border-0 shadow">
                <div class="card-header py-3 text-white" style="background-color: #ff5e14;">
                    <h4 class="my-0 font-weight-normal text-center">Premium</h4>
                </div>
                <div class="card-body">
                    <h2 class="card-title text-center">15 000 <small>FCFA</small></h2>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Nombre de biens illimité</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Visibilité prioritaire</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Support premium</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Statistiques avancées</li>
                        <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Gestion multi-utilisateurs</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 pb-4">
                    <button type="button" class="btn btn-lg btn-block" style="background-color: #02245b; color: white;">Souscrire</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="text-center mb-4" style="color: #02245b;">Questions Fréquentes</h3>
            <div class="accordion" id="faqAccordion">
                <div class="card">
                    <div class="card-header" id="headingOne" style="background-color: rgba(2, 36, 91, 0.05);">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="color: #02245b;">
                                Comment puis-je changer d'abonnement ?
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                        <div class="card-body">
                            Vous pouvez changer d'abonnement à tout moment dans votre espace personnel. Le changement prendra effet à la fin de votre période de facturation actuelle.
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTwo" style="background-color: rgba(2, 36, 91, 0.05);">
                        <h5 class="mb-0">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" style="color: #02245b;">
                                Puis-je annuler mon abonnement à tout moment ?
                            </button>
                        </h5>
                    </div>
                    <div id="collapseTwo"  aria-labelledby="headingTwo" data-parent="#faqAccordion">
                        <div class="card-body">
                            Oui, vous pouvez annuler votre abonnement à tout moment. L'accès aux fonctionnalités premium sera maintenu jusqu'à la fin de la période payée.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle between Proprietaire and Agence plans
    document.addEventListener('DOMContentLoaded', function() {
        const proprietaireRadio = document.getElementById('proprietaire');
        const agenceRadio = document.getElementById('agence');
        const proprietairePlans = document.getElementById('proprietaire-plans');
        const agencePlans = document.getElementById('agence-plans');
        
        proprietaireRadio.addEventListener('change', function() {
            if(this.checked) {
                proprietairePlans.style.display = 'flex';
                agencePlans.style.display = 'none';
            }
        });
        
        agenceRadio.addEventListener('change', function() {
            if(this.checked) {
                proprietairePlans.style.display = 'none';
                agencePlans.style.display = 'flex';
            }
        });
    });
</script>

<style>
    .card {
        transition: transform 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .btn-outline-primary:hover {
        background-color: #02245b;
        color: white !important;
    }
    .btn-outline-primary.active {
        background-color: #02245b;
        color: white !important;
    }
</style>
@endsection