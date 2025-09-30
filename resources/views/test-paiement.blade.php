<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Paiement CinetPay</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Test Paiement Mobile Money</h1>
    
    <form id="paymentForm">
        <div class="form-group">
            <label for="locataire_id">ID Locataire:</label>
            <input type="number" id="locataire_id" name="locataire_id" value="1" required>
        </div>
        
        <div class="form-group">
            <label for="methode_paiement">Méthode de paiement:</label>
            <select id="methode_paiement" name="methode_paiement" required>
                <option value="mobile_money">Mobile Money</option>
                <option value="virement">Virement</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="transaction_id">ID Transaction (optionnel):</label>
            <input type="text" id="transaction_id" name="transaction_id" value="TEST_<?php echo time(); ?>">
        </div>
        
        <button type="submit">Initier le Paiement</button>
    </form>
    
    <div id="result" class="result" style="display: none;"></div>

    <script>
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const locataireId = document.getElementById('locataire_id').value;
            
            try {
                const response = await fetch(`/api/tenant/${locataireId}/paiements`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                const resultDiv = document.getElementById('result');
                
                if (data.success) {
                    if (data.type === 'mobile_money_init') {
                        resultDiv.innerHTML = `
                            <h3>✅ Paiement Mobile Money Initialisé</h3>
                            <p><strong>Transaction ID:</strong> ${data.cinetpay_data.transaction_id}</p>
                            <p><strong>Montant:</strong> ${data.cinetpay_data.amount} XOF</p>
                            <p><strong>Description:</strong> ${data.cinetpay_data.description}</p>
                            <p><strong>URL de retour:</strong> ${data.cinetpay_data.return_url}</p>
                            
                            <h4>📱 Pour tester le paiement:</h4>
                            <ol>
                                <li>Utilisez le numéro de test: <strong>+2250700000000</strong></li>
                                <li>Code secret: <strong>000000</strong></li>
                                <li>Montant: <strong>${data.cinetpay_data.amount} XOF</strong></li>
                            </ol>
                            
                            <p><a href="/api/cinetpay/return?cpm_trans_id=${data.cinetpay_data.transaction_id}&cpm_result=00&cpm_amount=${data.cinetpay_data.amount}" 
                                  target="_blank" style="color: blue;">
                                  🔗 Cliquez ici pour simuler un paiement RÉUSSI
                            </a></p>
                            
                            <p><a href="/api/cinetpay/return?cpm_trans_id=${data.cinetpay_data.transaction_id}&cpm_result=01&cpm_amount=${data.cinetpay_data.amount}" 
                                  target="_blank" style="color: red;">
                                  🔗 Cliquez ici pour simuler un paiement ÉCHOUÉ
                            </a></p>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <h3>✅ Paiement Enregistré</h3>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    }
                    resultDiv.className = 'result success';
                } else {
                    resultDiv.innerHTML = `
                        <h3>❌ Erreur</h3>
                        <p>${data.message}</p>
                    `;
                    resultDiv.className = 'result error';
                }
                
                resultDiv.style.display = 'block';
                
            } catch (error) {
                console.error('Erreur:', error);
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = `
                    <h3>❌ Erreur de connexion</h3>
                    <p>${error.message}</p>
                `;
                resultDiv.className = 'result error';
                resultDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>