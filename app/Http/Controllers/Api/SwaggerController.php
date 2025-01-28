<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API de Campeonatos",
 *     description="API para gerenciamento de campeonatos e conteúdos",
 *     @OA\Contact(
 *         email="suporte@exemplo.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/championships",
     *     summary="Lista todos os campeonatos",
     *     tags={"Campeonatos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de campeonatos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Championship")
     *             )
     *         )
     *     )
     * )
     */

    /**
     * @OA\Post(
     *     path="/championships",
     *     summary="Cria um novo campeonato",
     *     tags={"Campeonatos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChampionshipRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Campeonato criado",
     *         @OA\JsonContent(ref="#/components/schemas/Championship")
     *     )
     * )
     */

    // ... mais documentação de endpoints ...
} 