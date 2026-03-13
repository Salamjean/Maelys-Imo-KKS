<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Propriétaires",
 *     description="Gestion des propriétaires par le commercial"
 * )
 */
class CommercialProprietaireApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/proprietaires",
     *      operationId="getCommercialProprietaires",
     *      tags={"Commercial - Propriétaires"},
     *      summary="Liste des propriétaires du commercial",
     *      description="Renvoie la liste des propriétaires enregistrés par le commercial connecté.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="proprietaires", type="array", @OA\Items(type="object"))
     *          )
     *      ),
     *      @OA\Response(response=403, description="Accès non autorisé")
     * )
     */
    public function index()
    {
        $commercial = auth()->user();
        
        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
             return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $proprietaires = Proprietaire::where('commercial_id', $commercial->code_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'proprietaires' => $proprietaires
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/proprietaires",
     *      operationId="storeProprietaireByCommercial",
     *      tags={"Commercial - Propriétaires"},
     *      summary="Ajouter un nouveau propriétaire",
     *      description="Permet à un commercial d'enregistrer un nouveau propriétaire.",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"name", "prenom", "email", "contact", "commune"},
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="prenom", type="string"),
     *                  @OA\Property(property="email", type="string", format="email"),
     *                  @OA\Property(property="contact", type="string"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="diaspora", type="string", enum={"Oui", "Non"}),
     *                  @OA\Property(property="gestion", type="string", enum={"agence", "personnelle"}),
     *                  @OA\Property(property="profil_image", type="string", format="binary")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Propriétaire créé avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="proprietaire", type="object")
     *          )
     *      ),
     *      @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(Request $request)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'diaspora' => 'nullable|string|in:Oui,Non',
            'gestion' => 'nullable|string|in:agence,personnelle',
            'profil_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Génération du code ID unique pour le propriétaire
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'PROP' . $randomNumber;
            } while (Proprietaire::where('code_id', $codeId)->exists());

            $profilImagePath = $request->hasFile('profil_image') 
                ? $request->file('profil_image')->store('proprietaires/profiles', 'public') 
                : null;

            $proprietaire = new Proprietaire();
            $proprietaire->code_id = $codeId;
            $proprietaire->name = $request->name;
            $proprietaire->prenom = $request->prenom;
            $proprietaire->email = $request->email;
            $proprietaire->contact = $request->contact;
            $proprietaire->commune = $request->commune;
            $proprietaire->diaspora = $request->diaspora ?? 'Non';
            $proprietaire->gestion = $request->gestion ?? 'proprietaire';
            $proprietaire->password = Hash::make('password'); // Mot de passe par défaut
            $proprietaire->profil_image = $profilImagePath;
            $proprietaire->commercial_id = $commercial->code_id;
            $proprietaire->save();

            // Envoi de l'e-mail de vérification / définition du mot de passe
            \App\Models\ResetCodePasswordProprietaire::where('email', $proprietaire->email)->delete();
            $code = rand(1000, 4000);
            \App\Models\ResetCodePasswordProprietaire::create([
                'code' => $code,
                'email' => $proprietaire->email,
            ]);

            \Illuminate\Support\Facades\Notification::route('mail', $proprietaire->email)
                ->notify(new \App\Notifications\SendEmailToProprietaireAfterRegistrationNotification($code, $proprietaire->email));

            return response()->json([
                'success' => true,
                'message' => 'Propriétaire enregistré avec succès. Un email a été envoyé pour la définition du mot de passe.',
                'proprietaire' => $proprietaire
            ], 201);

        } catch (Exception $e) {
            Log::error('API Error creating proprietaire par commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la création du propriétaire.'], 500);
        }
    }
}
