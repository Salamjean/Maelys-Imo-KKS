<div class="row g-3">
    <!-- Sol -->
    <div class="col-md-12 etat-item">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Sol</label>
                <select class="form-select etat-select" name="chambres[{{ $index }}][sol]">
                    <option value="">Sélectionner...</option>
                    <option value="bon">Bon état</option>
                    <option value="mauvais">Mauvais état</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label">Observations</label>
                <textarea class="form-control observation" name="chambres[{{ $index }}][observation_sol]" rows="2"></textarea>
            </div>
        </div>
    </div>
    
    <!-- Murs -->
    <div class="col-md-12 etat-item">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Murs</label>
                <select class="form-select etat-select" name="chambres[{{ $index }}][murs]">
                    <option value="">Sélectionner...</option>
                    <option value="bon">Bon état</option>
                    <option value="mauvais">Mauvais état</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label">Observations</label>
                <textarea class="form-control observation" name="chambres[{{ $index }}][observation_murs]" rows="2"></textarea>
            </div>
        </div>
    </div>
    
    <!-- Plafond -->
    <div class="col-md-12 etat-item">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Plafond</label>
                <select class="form-select etat-select" name="chambres[{{ $index }}][plafond]">
                    <option value="">Sélectionner...</option>
                    <option value="bon">Bon état</option>
                    <option value="mauvais">Mauvais état</option>
                </select>
            </div>
            <div class="col-md-9">
                <label class="form-label">Observations</label>
                <textarea class="form-control observation" name="chambres[{{ $index }}][observation_plafond]" rows="2"></textarea>
            </div>
        </div>
    </div>
</div>