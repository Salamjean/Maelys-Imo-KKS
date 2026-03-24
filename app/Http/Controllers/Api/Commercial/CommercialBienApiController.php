<?php

namespace App\Http\Controllers\Api\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Agence;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
 use Exception;

/**
 * @OA\Tag(
 *     name="Commercial - Biens",
 *     description="Gestion des biens par le commercial"
 * )
 */
class CommercialBienApiController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/commercial/biens",
     *      operationId="getCommercialBiens",
     *      tags={"Commercial - Biens"},
     *      summary="Liste des biens ajoutés par le commercial",
     *      description="Renvoie la liste des biens enregistrés par le commercial connecté.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="biens", type="array", @OA\Items(type="object"))
     *          )
     *      )
     * )
     */
    public function index()
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $biens = Bien::with(['agence', 'proprietaire'])
            ->where('commercial_id', $commercial->code_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'biens' => $biens
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/agences/{agence_id}/biens",
     *      operationId="storeBienForAgenceByCommercial",
     *      tags={"Commercial - Biens"},
     *      summary="Ajouter un bien pour une agence",
     *      description="Permet à un commercial d'ajouter un bien à une agence spécifique.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="agence_id",
     *          in="path",
     *          required=true,
     *          description="Code ID de l'agence",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"type", "utilisation", "description", "superficie", "avance", "caution", "prix", "commune", "disponibilite", "main_image", "additional_images1"},
     *                  @OA\Property(property="type", type="string", enum={"Appartement", "Maison", "Bureau"}),
     *                  @OA\Property(property="utilisation", type="string"),
     *                  @OA\Property(property="autre_utilisation", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="superficie", type="string"),
     *                  @OA\Property(property="nombre_de_chambres", type="string"),
     *                  @OA\Property(property="nombre_de_toilettes", type="string"),
     *                  @OA\Property(property="garage", type="string"),
     *                  @OA\Property(property="avance", type="integer"),
     *                  @OA\Property(property="caution", type="integer"),
     *                  @OA\Property(property="frais", type="string"),
     *                  @OA\Property(property="montant_total", type="string"),
     *                  @OA\Property(property="prix", type="string"),
     *                  @OA\Property(property="frais", type="integer"),
     *                  @OA\Property(property="montant_total", type="number", format="float"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="disponibilite", type="string"),
     *                  @OA\Property(property="proprietaire_id", type="string"),
     *                  @OA\Property(property="main_image", type="string", format="binary"),
     *                  @OA\Property(property="additional_images1", type="string", format="binary"),
     *                  @OA\Property(property="additional_images2", type="string", format="binary"),
     *                  @OA\Property(property="additional_images3", type="string", format="binary"),
     *                  @OA\Property(property="additional_images4", type="string", format="binary"),
     *                  @OA\Property(property="additional_images5", type="string", format="binary"),
     *                  @OA\Property(property="video_3d", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Bien ajouté avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="bien", type="object")
     *          )
     *      )
     * )
     */
    public function store(Request $request, $agence_id)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Vérification que l'agence appartient bien au commercial
        $agence = Agence::where('code_id', $agence_id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$agence) {
            return response()->json(['error' => 'Agence introuvable ou non gérée par ce commercial'], 404);
        }

        $request->validate([
            'proprietaire_id' => 'nullable|exists:proprietaires,code_id',
            'type' => 'required|string|in:Appartement,Maison,Bureau',
            'utilisation' => 'required|string',
            'autre_utilisation' => 'nullable|string',
            'description' => 'required|string|max:500',
            'superficie' => 'required|string',
            'nombre_de_chambres' => 'nullable|string',
            'nombre_de_toilettes' => 'nullable|string',
            'garage' => 'nullable|string',
            'avance' => 'required|integer|min:1|max:99',
            'caution' => 'required|integer|min:1|max:99',
            'frais' => 'nullable|integer|min:0',
            'montant_total' => 'nullable|numeric',
            'prix' => 'required|string',
            'commune' => 'required|string',
            'disponibilite' => 'required|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_3d' => 'nullable|string'
        ]);

        try {
            // Génération du code unique (numero_bien)
            $typePrefix = match ($request->type) {
                'Appartement' => 'AP',
                'Maison' => 'MA',
                'Bureau' => 'BU',
                default => 'AG',
            };

            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $numeroId = $typePrefix . $randomNumber;
            } while (Bien::where('numero_bien', $numeroId)->exists());

            $utilisation = $request->utilisation === 'Autre' && $request->autre_utilisation 
                ? $request->autre_utilisation 
                : $request->utilisation;

            $bien = new Bien();
            $bien->numero_bien = $numeroId;
            $bien->agence_id = $agence->code_id;
            $bien->proprietaire_id = $request->proprietaire_id;
            $bien->commercial_id = $commercial->code_id;
            $bien->type = $request->type;
            $bien->utilisation = $utilisation;
            $bien->description = $request->description;
            $bien->superficie = $request->superficie;
            $bien->nombre_de_chambres = $request->nombre_de_chambres;
            $bien->nombre_de_toilettes = $request->nombre_de_toilettes;
            $bien->garage = $request->garage;
            $bien->avance = $request->avance;
            $bien->caution = $request->caution;
            $bien->frais = $request->frais ?? 1;
            $bien->montant_total = $this->calculateMontantTotal(
                $request->prix,
                $request->avance,
                $request->caution,
                $bien->frais
            );
            $bien->prix = $request->prix;
            $bien->commune = $request->commune;
            $bien->date_fixe = $request->disponibilite;
            $bien->video_3d = $request->video_3d;
            $bien->status = 'Disponible';

            // Gestion des images
            if ($request->hasFile('main_image')) {
                $bien->image = $request->file('main_image')->store('biens_images', 'public');
            }

            for ($i = 1; $i <= 5; $i++) {
                $fieldName = 'additional_images' . $i;
                if ($request->hasFile($fieldName)) {
                    $imgAttr = 'image' . $i;
                    $bien->$imgAttr = $request->file($fieldName)->store('biens_images', 'public');
                }
            }

            $bien->save();

            return response()->json([
                'success' => true,
                'message' => 'Le bien a été ajouté avec succès pour l\'agence ' . $agence->name,
                'bien' => $bien
            ], 201);

        } catch (Exception $e) {
            Log::error('API Error creating bien for agence by commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'ajout du bien.'], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/proprietaires/{proprietaire_id}/biens",
     *      operationId="storeBienForProprietaireByCommercial",
     *      tags={"Commercial - Biens"},
     *      summary="Ajouter un bien pour un propriétaire",
     *      description="Permet à un commercial d'ajouter un bien à un propriétaire spécifique.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="proprietaire_id",
     *          in="path",
     *          required=true,
     *          description="Code ID du propriétaire",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"type", "utilisation", "description", "superficie", "avance", "caution", "prix", "commune", "disponibilite", "main_image", "additional_images1"},
     *                  @OA\Property(property="type", type="string", enum={"Appartement", "Maison", "Bureau"}),
     *                  @OA\Property(property="utilisation", type="string"),
     *                  @OA\Property(property="autre_utilisation", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="superficie", type="string"),
     *                  @OA\Property(property="nombre_de_chambres", type="string"),
     *                  @OA\Property(property="nombre_de_toilettes", type="string"),
     *                  @OA\Property(property="garage", type="string"),
     *                  @OA\Property(property="avance", type="integer"),
     *                  @OA\Property(property="caution", type="integer"),
     *                  @OA\Property(property="frais", type="string"),
     *                  @OA\Property(property="montant_total", type="string"),
     *                  @OA\Property(property="prix", type="string"),
     *                  @OA\Property(property="frais", type="integer"),
     *                  @OA\Property(property="montant_total", type="number", format="float"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="disponibilite", type="string"),
     *                  @OA\Property(property="main_image", type="string", format="binary"),
     *                  @OA\Property(property="additional_images1", type="string", format="binary"),
     *                  @OA\Property(property="additional_images2", type="string", format="binary"),
     *                  @OA\Property(property="additional_images3", type="string", format="binary"),
     *                  @OA\Property(property="additional_images4", type="string", format="binary"),
     *                  @OA\Property(property="additional_images5", type="string", format="binary"),
     *                  @OA\Property(property="video_3d", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Bien ajouté avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="bien", type="object")
     *          )
     *      )
     * )
     */
    public function storeForProprietaire(Request $request, $proprietaire_id)
    {
        $commercial = auth()->user();

        if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Vérification que le propriétaire appartient bien au commercial
        $proprietaire = Proprietaire::where('code_id', $proprietaire_id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$proprietaire) {
            return response()->json(['error' => 'Propriétaire introuvable ou non gérée par ce commercial'], 404);
        }

        $request->validate([
            'type' => 'required|string|in:Appartement,Maison,Bureau',
            'utilisation' => 'required|string',
            'autre_utilisation' => 'nullable|string',
            'description' => 'required|string|max:500',
            'superficie' => 'required|string',
            'nombre_de_chambres' => 'nullable|string',
            'nombre_de_toilettes' => 'nullable|string',
            'garage' => 'nullable|string',
            'avance' => 'required|integer|min:1|max:99',
            'caution' => 'required|integer|min:1|max:99',
            'frais' => 'nullable|integer|min:0',
            'montant_total' => 'nullable|numeric',
            'prix' => 'required|string',
            'commune' => 'required|string',
            'disponibilite' => 'required|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images1' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_3d' => 'nullable|string'
        ]);

        try {
            // Génération du code unique (numero_bien)
            $typePrefix = match ($request->type) {
                'Appartement' => 'AP',
                'Maison' => 'MA',
                'Bureau' => 'BU',
                default => 'AG',
            };

            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $numeroId = $typePrefix . $randomNumber;
            } while (Bien::where('numero_bien', $numeroId)->exists());

            $utilisation = $request->utilisation === 'Autre' && $request->autre_utilisation 
                ? $request->autre_utilisation 
                : $request->utilisation;

            $bien = new Bien();
            $bien->numero_bien = $numeroId;
            $bien->agence_id = null;
            $bien->proprietaire_id = $proprietaire->code_id;
            $bien->commercial_id = $commercial->code_id;
            $bien->type = $request->type;
            $bien->utilisation = $utilisation;
            $bien->description = $request->description;
            $bien->superficie = $request->superficie;
            $bien->nombre_de_chambres = $request->nombre_de_chambres;
            $bien->nombre_de_toilettes = $request->nombre_de_toilettes;
            $bien->garage = $request->garage;
            $bien->avance = $request->avance;
            $bien->caution = $request->caution;
            $bien->frais = $request->frais ?? 1;
            $bien->montant_total = $this->calculateMontantTotal(
                $request->prix,
                $request->avance,
                $request->caution,
                $bien->frais
            );
            $bien->prix = $request->prix;
            $bien->commune = $request->commune;
            $bien->date_fixe = $request->disponibilite;
            $bien->video_3d = $request->video_3d;
            $bien->status = 'Disponible';

            // Gestion des images
            if ($request->hasFile('main_image')) {
                $bien->image = $request->file('main_image')->store('biens_images', 'public');
            }

            for ($i = 1; $i <= 5; $i++) {
                $fieldName = 'additional_images' . $i;
                if ($request->hasFile($fieldName)) {
                    $imgAttr = 'image' . $i;
                    $bien->$imgAttr = $request->file($fieldName)->store('biens_images', 'public');
                }
            }

            $bien->save();

            return response()->json([
                'success' => true,
                'message' => 'Le bien a été ajouté avec succès pour le propriétaire ' . $proprietaire->name,
                'bien' => $bien
            ], 201);

        } catch (Exception $e) {
            Log::error('API Error creating bien for proprietaire by commercial: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'ajout du bien.'], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/commercial/biens/{id}",
     *      operationId="getCommercialBienDetails",
     *      tags={"Commercial - Biens"},
     *      summary="Détails d'un bien",
     *      description="Renvoie les détails d'un bien spécifique géré par le commercial.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID du bien",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="bien", type="object")
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        $commercial = auth()->user();

        $bien = Bien::with(['agence', 'proprietaire'])
            ->where('id', $id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$bien) {
            return response()->json(['error' => 'Bien introuvable'], 404);
        }

        return response()->json([
            'success' => true,
            'bien' => $bien
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/commercial/biens/{id}/update",
     *      operationId="updateBienByCommercial",
     *      tags={"Commercial - Biens"},
     *      summary="Modifier un bien",
     *      description="Permet à un commercial de modifier les informations d'un bien qu'il a ajouté.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID du bien",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="type", type="string", enum={"Appartement", "Maison", "Bureau"}),
     *                  @OA\Property(property="utilisation", type="string"),
     *                  @OA\Property(property="autre_utilisation", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="superficie", type="string"),
     *                  @OA\Property(property="nombre_de_chambres", type="string"),
     *                  @OA\Property(property="nombre_de_toilettes", type="string"),
     *                  @OA\Property(property="garage", type="string"),
     *                  @OA\Property(property="avance", type="integer"),
     *                  @OA\Property(property="caution", type="integer"),
     *                  @OA\Property(property="prix", type="string"),
     *                  @OA\Property(property="frais", type="integer"),
     *                  @OA\Property(property="montant_total", type="number", format="float"),
     *                  @OA\Property(property="commune", type="string"),
     *                  @OA\Property(property="disponibilite", type="string"),
     *                  @OA\Property(property="main_image", type="string", format="binary"),
     *                  @OA\Property(property="additional_images1", type="string", format="binary"),
     *                  @OA\Property(property="additional_images2", type="string", format="binary"),
     *                  @OA\Property(property="additional_images3", type="string", format="binary"),
     *                  @OA\Property(property="additional_images4", type="string", format="binary"),
     *                  @OA\Property(property="additional_images5", type="string", format="binary"),
     *                  @OA\Property(property="video_3d", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bien mis à jour avec succès"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $commercial = auth()->user();

        $bien = Bien::where('id', $id)
            ->where('commercial_id', $commercial->code_id)
            ->first();

        if (!$bien) {
            return response()->json(['error' => 'Bien introuvable ou vous n\'avez pas les droits'], 404);
        }

        $request->validate([
            'type' => 'nullable|string|in:Appartement,Maison,Bureau',
            'utilisation' => 'nullable|string',
            'autre_utilisation' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'superficie' => 'nullable|string',
            'nombre_de_chambres' => 'nullable|string',
            'nombre_de_toilettes' => 'nullable|string',
            'garage' => 'nullable|string',
            'avance' => 'nullable|integer|min:1|max:99',
            'caution' => 'nullable|integer|min:1|max:99',
            'prix' => 'nullable|string',
            'frais' => 'nullable|integer|min:0',
            'montant_total' => 'nullable|numeric',
            'commune' => 'nullable|string',
            'disponibilite' => 'nullable|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images4' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images5' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_3d' => 'nullable|string'
        ]);

        try {
            if ($request->has('type')) $bien->type = $request->type;
            
            if ($request->has('utilisation')) {
                $utilisation = $request->utilisation === 'Autre' && $request->autre_utilisation 
                    ? $request->autre_utilisation 
                    : $request->utilisation;
                $bien->utilisation = $utilisation;
            }

            if ($request->has('description')) $bien->description = $request->description;
            if ($request->has('superficie')) $bien->superficie = $request->superficie;
            if ($request->has('nombre_de_chambres')) $bien->nombre_de_chambres = $request->nombre_de_chambres;
            if ($request->has('nombre_de_toilettes')) $bien->nombre_de_toilettes = $request->nombre_de_toilettes;
            if ($request->has('garage')) $bien->garage = $request->garage;
            if ($request->has('avance')) $bien->avance = $request->avance;
            if ($request->has('caution')) $bien->caution = $request->caution;
            if ($request->has('prix')) $bien->prix = $request->prix;
            if ($request->has('frais')) $bien->frais = $request->frais;

            // Recalculer le montant total si l'un des composants change
            if ($request->hasAny(['prix', 'avance', 'caution', 'frais'])) {
                $bien->montant_total = $this->calculateMontantTotal(
                    $bien->prix,
                    $bien->avance,
                    $bien->caution,
                    $bien->frais ?? 1
                );
            }
            if ($request->has('commune')) $bien->commune = $request->commune;
            if ($request->has('disponibilite')) $bien->date_fixe = $request->disponibilite;
            if ($request->has('video_3d')) $bien->video_3d = $request->video_3d;

            // Gestion des images
            if ($request->hasFile('main_image')) {
                if ($bien->image) Storage::disk('public')->delete($bien->image);
                $bien->image = $request->file('main_image')->store('biens_images', 'public');
            }

            for ($i = 1; $i <= 5; $i++) {
                $fieldName = 'additional_images' . $i;
                if ($request->hasFile($fieldName)) {
                    $imgAttr = 'image' . $i;
                    if ($bien->$imgAttr) Storage::disk('public')->delete($bien->$imgAttr);
                    $bien->$imgAttr = $request->file($fieldName)->store('biens_images', 'public');
                }
            }

            $bien->save();
 
             return response()->json([
                 'success' => true,
                 'message' => 'Le bien a été mis à jour avec succès',
                 'bien' => $bien
             ]);
 
         } catch (Exception $e) {
             Log::error('API Error updating bien by commercial: ' . $e->getMessage());
             return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour du bien.'], 500);
         }
     }
 
     /**
      * @OA\Delete(
      *      path="/api/commercial/biens/{id}",
      *      operationId="deleteBienByCommercial",
      *      tags={"Commercial - Biens"},
      *      summary="Supprimer un bien",
      *      description="Permet à un commercial de supprimer un bien qu'il a ajouté, à condition que celui-ci ne soit pas loué.",
      *      security={{"sanctum":{}}},
      *      @OA\Parameter(
      *          name="id",
      *          in="path",
      *          required=true,
      *          description="ID du bien",
      *          @OA\Schema(type="integer")
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Bien supprimé avec succès",
      *          @OA\JsonContent(
      *              @OA\Property(property="success", type="boolean", example=true),
      *              @OA\Property(property="message", type="string")
      *          )
      *      ),
      *      @OA\Response(
      *          response=403,
      *          description="Accès refusé ou bien loué"
      *      ),
      *      @OA\Response(
      *          response=404,
      *          description="Bien introuvable"
      *      )
      * )
      */
     public function destroy($id)
     {
         $commercial = auth()->user();
 
         if (!$commercial || !($commercial instanceof \App\Models\Commercial)) {
             return response()->json(['error' => 'Accès non autorisé'], 403);
         }
 
         $bien = Bien::where('id', $id)
             ->where('commercial_id', $commercial->code_id)
             ->first();
 
         if (!$bien) {
             return response()->json(['error' => 'Bien introuvable ou vous n\'avez pas les droits'], 404);
         }
 
         // Vérifier si le bien est loué ou a des dépendances critiques
         if ($bien->status === 'Loué' || $bien->locataire()->exists()) {
             return response()->json(['error' => 'Impossible de supprimer un bien déjà loué ou ayant un locataire actif.'], 403);
         }
 
         try {
             DB::beginTransaction();
 
             // Suppression des images du storage
             $images = $bien->getImages();
             foreach ($images as $imagePath) {
                 if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                     Storage::disk('public')->delete($imagePath);
                 }
             }
 
             // Suppression du bien (les relations devraient être gérées par cascade si configuré, 
             // sinon on vérifie les dépendances au préalable comme fait ci-dessus)
             $bien->delete();
 
             DB::commit();
 
             return response()->json([
                 'success' => true,
                 'message' => 'Le bien a été supprimé avec succès.'
             ]);
 
         } catch (Exception $e) {
             DB::rollBack();
             Log::error('API Error deleting bien by commercial: ' . $e->getMessage());
             return response()->json(['error' => 'Une erreur est survenue lors de la suppression du bien.'], 500);
         }
     }
 
    /**
     * Calcule le montant total à l'entrée.
     * Formule : (avance + caution + frais) * prix
     */
    private function calculateMontantTotal($prix, $avance, $caution, $frais)
    {
        $prix = (float) str_replace([' ', ' '], '', $prix);
        $avance = (int) $avance;
        $caution = (int) $caution;
        $frais = (int) $frais;
        
        return ($avance + $caution + $frais) * $prix;
    }
}
