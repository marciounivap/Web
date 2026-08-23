<?php

namespace Api\Controllers;

// Importações das classes necessárias
use Psr\Http\Message\ResponseInterface as Response; // Interface para respostas HTTP PSR-7
use Psr\Http\Message\ServerRequestInterface as Request; // Interface para requisições HTTP PSR-7
use Api\Services\FuncionarioService; // Serviço de negócio para operações com funcionários

/**
 * Classe FuncionarioController
 *
 * Responsável por controlar os endpoints da API REST para a entidade Funcionario.
 * Implementa o padrão Controller da arquitetura MVC, atuando como intermediário
 * entre as rotas (Router) e a camada de serviço (Service).
 *
 * FLUXO DE UMA REQUISIÇÃO:
 * 1. Rota chama o método do controller
 * 2. Controller extrai dados da requisição
 * 3. Controller chama métodos do Service
 * 4. Controller formata resposta padronizada
 * 5. Retorna Response com JSON e status HTTP
 *
 * PADRÃO ADOTADO:
 * - Uso de stdClass com json_decode($body)
 * - Acesso via objeto: $objPHP->funcionario->email
 * - Assinaturas em uma única linha
 */
class FuncionarioController
{
    /**
     * Serviço responsável pelas regras de negócio.
     *
     * @var FuncionarioService
     */
    private FuncionarioService $funcionarioService;

    /**
     * Construtor com injeção de dependência.
     *
     * @param FuncionarioService $funcionarioService
     */
    public function __construct(FuncionarioService $funcionarioService)
    {
        error_log("🟦 FuncionarioController::__construct()");
        $this->funcionarioService = $funcionarioService;
    }
    /**
     * Realiza autenticação do funcionário.
     *
     * Endpoint:
     * POST /api/v1/funcionarios/login
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function loginController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::loginController()");

        $body = $request->getBody()->getContents();

        $objPHP = json_decode($body, true);

        $resultado = $this->funcionarioService->loginService($objPHP['funcionario']);

        $funcionario = $resultado['funcionario'];

        $token = $resultado['token'];

        $resposta = [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'funcionario' => [
                    'idFuncionario' => $funcionario->getIdFuncionario(),
                    'nomeFuncionario' => $funcionario->getNomeFuncionario(),
                    'email' => $funcionario->getEmail(),
                    'recebeValeTransporte' => $funcionario->getRecebeValeTransporte(),
                    'cargo' => [
                        'idCargo' => $funcionario->getCargo()->getIdCargo(),
                        'nomeCargo' => $funcionario->getCargo()->getNomeCargo()
                    ]
                ],
                'token' => $token
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    /**
     * Cria novo funcionário.
     *
     * Endpoint: POST /api/v1/funcionarios
     *
     * @return Response
     */
    public function createController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::createController()");

        // Lê JSON bruto e converte para stdClass
        $body = $request->getBody()->getContents();
        $objPHP = json_decode($body);

        // Service recebe objeto funcionario
        $resultado = $this->funcionarioService->createService($objPHP->funcionario);

        $resposta = [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso',
            'data' => [
                'funcionarios' => [
                    $resultado
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    /**
     * Lista todos os funcionários.
     *
     * Endpoint: GET /api/v1/funcionarios
     *
     * @return Response
     */
    public function findAllController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::findAllController()");

        $lista = $this->funcionarioService->findAll();

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => [
                'funcionarios' => $lista
            ]
        ];

        $response->getBody()->write(
            json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Busca funcionário por ID.
     *
     * Endpoint: GET /api/v1/funcionarios/{idFuncionario}
     *
     * @return Response
     */
    public function findByidController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::findByidController()");

        $id = (int) $args['idFuncionario'];

        $funcionario = $this->funcionarioService->findByIdService($id);

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => [
                'funcionarios' => [
                    $funcionario
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Retorna contagem total.
     *
     * Endpoint: GET /api/v1/funcionarios/count
     *
     * @return Response
     */
    public function countController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::countController()");

        $qtd = $this->funcionarioService->countService();

        $resposta = [
            'success' => true,
            'message' => 'Executado com sucesso',
            'data' => [
                'count' => $qtd
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Atualiza funcionário existente.
     *
     * Endpoint: PUT /api/v1/funcionarios/{idFuncionario}
     *
     * @return Response
     */
    public function updateController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::updateController()");

        $id = (int) $args['idFuncionario'];

        // Lê JSON bruto e converte para stdClass
        $body = $request->getBody()->getContents();
        $objPHP = json_decode($body);

        /**
         * CORREÇÃO:
         * Seu Service ainda espera ARRAY no segundo parâmetro:
         * updateService(int $idFuncionario, array $requestBody)
         *
         * Então convertemos stdClass para array associativo.
         */
        $arrayPHP = json_decode($body, true);

        $resultado = $this->funcionarioService->updateService($id, $arrayPHP);

        $resposta = [
            'success' => true,
            'message' => 'Atualizado com sucesso',
            'data' => [
                'funcionarios' => [
                    $resultado
                ]
            ]
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Remove funcionário.
     *
     * Endpoint: DELETE /api/v1/funcionarios/{idFuncionario}
     *
     * @return Response
     */
    public function deleteController(Request $request, Response $response, array $args): Response
    {
        error_log("🔵 FuncionarioController::deleteController()");

        $id = (int) $args['idFuncionario'];

        $excluiu = $this->funcionarioService->deleteService($id);

        $status = $excluiu ? 204 : 404;
        $mensagem = $excluiu
            ? 'Excluído com sucesso'
            : 'Funcionário não encontrado';

        $resposta = [
            'success' => $excluiu,
            'message' => $mensagem
        ];

        $response->getBody()->write(json_encode($resposta));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}