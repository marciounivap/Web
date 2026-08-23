<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Services\CargoService;

/**
 * Classe CargoController
 *
 * Responsável pelos endpoints REST da entidade Cargo.
 *
 * PADRÃO:
 * - Assinaturas em uma linha
 * - JSON convertido para stdClass
 * - Controller delega regras para Service
 */
class CargoController
{
    /**
     * Serviço da entidade Cargo.
     *
     * @var CargoService
     */
    private CargoService $cargoService;

    /**
     * Injeção de dependência.
     *
     * @param CargoService $cargoServiceDependency
     */
    public function __construct(CargoService $cargoServiceDependency)
    {
              
        error_log("🟦 CargoController::__construct()");
        $this->cargoService = $cargoServiceDependency;
    }

    /**
     * Cria novo cargo.
     *
     * Endpoint:
     * POST /api/v1/cargos
     *
     * JSON esperado:
     * {
     *   "cargo": {
     *      "nomeCargo": "Administrador"
     *   }
     * }
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function createController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::createController()");

        $body = $request->getBody()->getContents();
        $objPHP = json_decode($body);

        $novoCargo = $this->cargoService->createService($objPHP);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => [
                'cargos' => [
                    [
                        'idCargo' => $novoCargo->getIdCargo(),
                        'nomeCargo' => $novoCargo->getNomeCargo()
                    ]
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    /**
     * Lista todos os cargos.
     *
     * Endpoint:
     * GET /api/v1/cargos
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function findAllController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::findAllController()");

        $cargos = $this->cargoService->findAllService();

        $resposta = [
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => [
                'cargos' => $cargos
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Busca cargo por ID.
     *
     * Endpoint:
     * GET /api/v1/cargos/{idCargo}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function findByIdController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::findByIdController()");

        $idCargo = (int) $args['idCargo'];
        $cargo = $this->cargoService->findByIdService($idCargo);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => [
                'cargos' => $cargo
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Atualiza cargo.
     *
     * Endpoint:
     * PUT /api/v1/cargos/{idCargo}
     *
     * JSON esperado:
     * {
     *   "cargo": {
     *      "nomeCargo": "Novo Nome"
     *   }
     * }
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function updateController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::updateController()");

        $idCargo = (int) $args['idCargo'];

        $body = $request->getBody()->getContents();
        $objPHP = json_decode($body);

        $nomeCargo = $objPHP->cargo->nomeCargo;

        $this->cargoService->updateService($idCargo, $nomeCargo);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => [
                'cargos' => [
                    [
                        'idCargo' => $idCargo,
                        'nomeCargo' => $nomeCargo
                    ]
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Exclui cargo.
     *
     * Endpoint:
     * DELETE /api/v1/cargos/{idCargo}
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function deleteController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::deleteController()");

        $idCargo = (int) $args['idCargo'];

        $this->cargoService->deleteService($idCargo);

        $resposta = [
            'success' => true,
            'message' => 'Excluído com sucesso',
            'data' => [
                'cargos' => [
                    [
                        'idCargo' => $idCargo
                    ]
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Conta total de cargos.
     *
     * Endpoint:
     * GET /api/v1/cargos/count
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function countController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 CargoController::countController()");

        $total = $this->cargoService->countService();

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => [
                'count' => $total
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}