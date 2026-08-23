<?php

namespace Api\Routes;

use Slim\App;
use Api\Controllers\CargoController;
use Api\Middlewares\Cargo\ValidateCargoBody;
use Api\Middlewares\Cargo\ValidateCargoId;
use Api\Middlewares\Funcionario\ValidateFuncionarioToken;
use Api\Middlewares\Funcionario\ValidateAdministrador;

/**
 * Classe responsável por registrar as rotas do recurso Cargo.
 *
 * Endpoints disponíveis:
 * - POST   /cargos
 * - GET    /cargos
 * - GET    /cargos/count
 * - GET    /cargos/{idCargo}
 * - PUT    /cargos/{idCargo}
 * - DELETE /cargos/{idCargo}
 */
class CargoRouter
{
    /**
     * Instância da aplicação Slim.
     *
     * @var App
     */
    private App $app;

    /**
     * Recebe a instância principal da aplicação.
     *
     * @param App $app Aplicação Slim.
     */
    public function __construct(App $app)
    {
        error_log("🟥 CargoRouter::__construct()");
        $this->app = $app;
    }

    /**
     * Registra todas as rotas relacionadas ao recurso Cargo.
     *
     * Estrutura esperada do JSON:
     *
     * {
     *   "cargo": {
     *     "nomeCargo": "teste"
     *   }
     * }
     *
     * IMPORTANTE:
     * No Slim Framework, os middlewares executam em ordem inversa
     * à ordem em que são adicionados com ->add().
     *
     * O último middleware adicionado executa primeiro.
     *
     * @return void
     */
    public function setupRoutes(): void
    {
        error_log("🔴 CargoRouter::setupRoutes()");
        /**
         * =========================================================
         * POST /cargos
         * =========================================================
         * Cria um novo cargo.
         *
         * Body:
         * {
         *   "cargo": {
         *     "nomeCargo": "teste"
         *   }
         * }
         *
         * Ordem de execução:
         * 1. ValidateCargoBody
         * 2. CargoController::createController
         */
        $this->app->post(
            '/cargos',
            [CargoController::class, 'createController']
        )
            ->add(ValidateCargoBody::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateFuncionarioToken::class);

        /**
         * =========================================================
         * GET /cargos
         * =========================================================
         * Lista todos os cargos.
         *
         * Ordem de execução:
         * 1. CargoController::findAllController
         */
        $this->app->get(
            '/cargos',
            [CargoController::class, 'findAllController']
        )
            ->add(ValidateFuncionarioToken::class);

        /**
         * =========================================================
         * GET /cargos/count
         * =========================================================
         * Retorna a quantidade total de cargos.
         *
         * Ordem de execução:
         * 1. CargoController::countController
         */
        $this->app->get(
            '/cargos/count',
            [CargoController::class, 'countController']
        )->add(ValidateFuncionarioToken::class);

        /**
         * =========================================================
         * GET /cargos/{idCargo}
         * =========================================================
         * Busca um cargo pelo ID.
         *
         * Ordem de execução:
         * 1. ValidateCargoId
         * 2. CargoController::findByIdController
         */
        $this->app->get(
            '/cargos/{idCargo}',
            [CargoController::class, 'findByIdController']
        )
            ->add(ValidateCargoId::class)
            ->add(ValidateFuncionarioToken::class);
        ;

        /**
         * =========================================================
         * PUT /cargos/{idCargo}
         * =========================================================
         * Atualiza um cargo existente.
         *
         * Body:
         * {
         *   "cargo": {
         *     "nomeCargo": "teste"
         *   }
         * }
         *
         * Ordem de execução:
         * 1. ValidateCargoId
         * 2. ValidateCargoBody
         * 3. CargoController::updateController
         */
        $this->app->put(
            '/cargos/{idCargo}',
            [CargoController::class, 'updateController']
        )
            ->add(ValidateCargoBody::class)
            ->add(ValidateCargoId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateFuncionarioToken::class);
        ;

        /**
         * =========================================================
         * DELETE /cargos/{idCargo}
         * =========================================================
         * Remove um cargo pelo ID.
         *
         * Ordem de execução:
         * 1. ValidateCargoId
         * 2. CargoController::deleteController
         */
        $this->app->delete(
            '/cargos/{idCargo}',
            [CargoController::class, 'deleteController']
        )
            ->add(ValidateCargoId::class)
            ->add(ValidateAdministrador::class)
            ->add(ValidateFuncionarioToken::class);
    }
}